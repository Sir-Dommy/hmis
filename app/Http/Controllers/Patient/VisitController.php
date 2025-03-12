<?php

namespace App\Http\Controllers\Patient;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateBillItemsRequest;
use App\Models\Admin\Clinic;
use App\Models\Admin\Department;
use App\Models\Admin\PaymentType;
use App\Models\Admin\Scheme;
use App\Models\Admin\ServiceRelated\ServicePrice;
use App\Models\Admin\VisitType;
use App\Models\Bill\Bill;
use App\Models\Patient\Visit;
use App\Models\Patient\Visits\VisitClinic;
use App\Models\Patient\Visits\VisitDepartment;
use App\Models\Patient\Visits\VisitInsuranceDetail;
use App\Models\Patient\Visits\VisitPaymentType;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VisitController extends Controller
{
    //saving a new emergency visit
    public function createVisit(Request $request){

        //ValidateBillItemsRequest
        //$request->validated();
        
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'department' => 'required|string|exists:departments,name',
            'clinic'=>'required|string|exists:clinics,name',
            'visit_type'=>'required|string|exists:visit_types,name',
            // 'doctor'=>'nullable',
            // 'consultation_type'=>'nullable',
            // 'consultation_category'=>'nullable',
            // 'service'=>'required|string|exists:services,name',
            'schemes' => 'nullable',
            'payment_types'=>'required',
            'service_price_details'=>'required',
            'bar_code'=>'nullable|string',
        
        ]);

        // // ensure that there is no existing open visit for the patient...............
        // count(Visit::where('patient_id', $request->patient_id)
        //                         ->where('open', 1)
        //                         ->get()) > 0 ? throw new InputsValidationException("An open visit for patient ". $request->patient_id . " Already exists!!!!!!") : null;
        

        $department = Department::where('name', $request->department)->get("id");
        $clinic = Clinic::where('name', $request->clinic)->get("id");
        $visit_type = VisitType::selectVisitTypes(null, $request->visit_type);


        DB::beginTransaction();

        try{
            $visit = Visit::create([
                'patient_id' => $request->patient_id, 
                'visit_type_id' => $visit_type[0]['id'],
                'bar_code'=>$request->bar_code,
                'stage'=>0,
                'created_by' => User::getLoggedInUserId()
            ]);
            //echo ("TUKO NA VISIT ID " . )
            //save visit department
            VisitDepartment::create([
                'visit_id' => $visit->id,
                'department_id' => $department[0]['id']
            ]);

            // save visit clinic
            VisitClinic::create([
                'visit_id' => $visit->id,
                'clinic_id' => $clinic[0]['id']
            ]);

    
            $this->validateAndSaveVisitPaymentType( (object) $request->payment_types, $visit->id, $request->schemes);
    
            foreach($request->payment_types as $payment_type){
                if($payment_type['insurance'] == 1){
                    
                    foreach($request->schemes as $scheme){
                        

                        $validator = Validator::make((array) $scheme, [
                            'claim_number' => 'required|string',
                            'available_balance' => 'required|numeric',
                            'insurer' => 'required|string|exists:schemes,name',
                        ]);

                        if ($validator->fails()) {
                            return response()->json(['errors' => $validator->errors()], 422);
                        }

                        $existing_scheme = Scheme::where('name', $scheme['insurer'])->get("id");
                        
                        count($existing_scheme) < 1  ? throw new InputsValidationException("Provide a valid insurer!!!!!!!!") : null;

                        VisitInsuranceDetail::create([
                            'visit_id' => $visit->id,
                            'claim_number' => $scheme['claim_number'],
                            'available_balance' => $scheme['available_balance'],
                            'scheme_id' => $existing_scheme[0]['id'],
                            'signature' => $request->signature,
                        ]);
                    }
                }
            }

            //now create bill and its related bill items
            Bill::createBillAndBillItems($request, $visit->id);

            //Commit transaction
            DB::commit();

        }

        catch(Exception $e){
            DB::rollBack();
            throw new Exception($e);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a visit with id: ". $visit->id);

        return response()->json(
            Visit::selectVisits($visit->id)
        ,200);

    }

    // updating a visit...
    public function updateVisit(Request $request){
        $request->validate([
            'id' => 'required|exists:visits,id',
            'patient_id' => 'required|exists:patients,id',
            'department' => 'required|string|exists:departments,name',
            'clinic'=>'required|string|exists:clinics,name',
            'visit_type'=>'required|string|exists:visit_types,name',
            'schemes' => 'nullable',
            'payment_types'=>'required',
            'bar_code'=>'nullable|string',
        ]);
        

        $department = Department::where('name', $request->department)->get("id");
        $clinic = Clinic::where('name', $request->clinic)->get("id");
        $visit_type = VisitType::selectVisitTypes(null, $request->visit_type);

        DB::beginTransaction();

        try{
            Visit::where('id', $request->id)
            ->update([
                'patient_id' => $request->patient_id, 
                'visit_type_id' => $visit_type[0]['id'],
                'stage'=> 0,
                'bar_code'=>$request->bar_code,
                'updated_by' => User::getLoggedInUserId()
            ]);

        
            DB::commit();
        }

        catch(Exception $e){
            DB::rollBack();

            throw new Exception($e);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a visit with id: ". $request->id);

        return response()->json(
            Visit::selectVisits($request->id)
        ,200);

    }

    //Getting a single visit 
    public function getSingleVisit($id){

        $visit = Visit::selectVisits($id);

        if(count($visit) < 1){
            throw new NotFoundException(APIConstants::NAME_VISIT);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a visit with id: ". $visit[0]['id']);

        return response()->json(
            $visit
        ,200);
    }


    //getting all patients Details
    public function getAllVisits(){

        $visits = Visit::selectVisits(null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all visits");

        return response()->json(
            $visits
        ,200);
    }

    public function softDeleteVisit($id){
            
        $existing = Visit::selectVisits($id);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_VISIT. " with id: ". $id);
        }
        
        Visit::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a visit with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    public function permanentlyDelete($id){
            
        $existing = Visit::where("id",$id)->get();

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_VISIT. " with id: ". $id);
        }
        
        Visit::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted a visit with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    private function validateAndSaveVisitPaymentType($payment_types, $visit_id, $schemes){


        foreach($payment_types as $payment_type){

            if($payment_type['cash'] == 1){
                $payment_method = "Cash";
            }
    
            else if($payment_type['insurance'] == 1){
                $payment_method = "Insurance";
                
                !$schemes ? throw new InputsValidationException("If Insurance is one of the payment types you must provide scheme details eg... claim number") : null;
            }
    
            $existing_method = PaymentType::selectPaymentTypes(null, $payment_method);
    
            if(count($existing_method) < 1){
                DB::rollBack();
                
                throw new NotFoundException(APIConstants::NAME_PAYMENT_TYPE ." $payment_method");
    
            }
    
            VisitPaymentType::create([
                'visit_id' => $visit_id,
                'payment_type_id' => $existing_method[0]['id']
            ]);
        }

        
    }

    public function listServicePrices(Request $request){
        $cash_related_prices_array = [];
        $schemes_related_prices_array = [];

        //return $request->payment_types;
        if(is_array($request->payment_types) && count($request->payment_types) > 0){
            foreach($request->payment_types as $payment_type){
                if($payment_type['cash'] == 1){      
                    //merge results with those of result types depending on lab requests
    
                    if(is_array($request->lab_test_types) && count($request->lab_test_types) > 0){
                        foreach($request->lab_test_types as $lab_test_type){
                            $cash_related_prices_array = array_merge($cash_related_prices_array, (ServicePrice::selectFirstExactServicePrice($request->id, $request->service, $request->department, $request->consultation_category, $request->clinic, "cash", null, null,
                            $request->consultation_type, $request->visit_type, $request->doctor, $request->current_time, $request->duration, $lab_test_type["name"], null, null, null, $request->branch, $request->building,
                            $request->wing, $request->ward, $request->office)->toArray()));
                        }
                    }
                        
                    //merge results with those of result types depending on image test types requests
                    if(is_array($request->image_test_types) && count($request->image_test_types) > 0){
    
                        foreach($request->image_test_types as $image_test_type){
                            $cash_related_prices_array = array_merge($cash_related_prices_array, (ServicePrice::selectFirstExactServicePrice($request->id, $request->service, $request->department, $request->consultation_category, $request->clinic, "cash", null, null,
                            $request->consultation_type, $request->visit_type, $request->doctor, $request->current_time, $request->duration, null, $image_test_type["name"], null, null, $request->branch, $request->building,
                            $request->wing, $request->ward, $request->office)->toArray()));
                        }
                    }
        
        
        
                    //merge results with those of result types depending on drugs requests
                    if(is_array($request->drugs) && count($request->drugs) > 0){
                        foreach($request->drugs as $drug){
                            $cash_related_prices_array = array_merge($cash_related_prices_array, (ServicePrice::selectFirstExactServicePrice($request->id, $request->service, $request->department, $request->consultation_category, $request->clinic, "cash", null, null,
                            $request->consultation_type, $request->visit_type, $request->doctor, $request->current_time, $request->duration, null, null, $drug["name"], $drug["brand"], $request->branch, $request->building,
                            $request->wing, $request->ward, $request->office)->toArray()));
                        }
                    }
                    
    
                    // return response()->json($cash_related_prices_array, 200);
    
                }
    
                //now check for insurance
                if($payment_type['insurance'] == 1){
                    if(is_array($request->schemes) && count($request->schemes) > 0){
                        foreach($request->schemes as $scheme){        
                            //merge results with those of result types depending on lab requests
                            if(is_array($request->lab_test_types) && count($request->lab_test_types) > 0){
                                foreach($request->lab_test_types as $lab_test_type){
                                    $schemes_related_prices_array = array_merge($schemes_related_prices_array, (ServicePrice::selectFirstExactServicePrice($request->id, $request->service, $request->department, $request->consultation_category, $request->clinic, $request->payment_type, $scheme["name"], $scheme["scheme_type"],
                                    $request->consultation_type, $request->visit_type, $request->doctor, $request->current_time, $request->duration, $lab_test_type["name"], null, null, null, $request->branch, $request->building,
                                    $request->wing, $request->ward, $request->office)->toArray()));
                                }
                            }
                
                            //merge results with those of result types depending on image test types requests
                            if(is_array($request->image_test_types) && count($request->image_test_types) > 0){
                                foreach($request->image_test_types as $image_test_type){
                                    $schemes_related_prices_array = array_merge($schemes_related_prices_array, (ServicePrice::selectFirstExactServicePrice($request->id, $request->service, $request->department, $request->consultation_category, $request->clinic, $request->payment_type, $scheme["name"], $scheme["scheme_type"],
                                    $request->consultation_type, $request->visit_type, $request->doctor, $request->current_time, $request->duration, null, $image_test_type["name"], null, null, $request->branch, $request->building,
                                    $request->wing, $request->ward, $request->office)->toArray()));
                                }
                            }
                
                
                
                            //merge results with those of result types depending on drugs requests
                            if(is_array($request->drugs) && count($request->drugs) > 0){
                                foreach($request->drugs as $drug){
                                    $schemes_related_prices_array = array_merge($schemes_related_prices_array, (ServicePrice::selectFirstExactServicePrice($request->id, $request->service, $request->department, $request->consultation_category, $request->clinic, $request->payment_type, $scheme["name"], $scheme["scheme_type"],
                                    $request->consultation_type, $request->visit_type, $request->doctor, $request->current_time, $request->duration, null, null, $drug["name"], $drug["brand"], $request->branch, $request->building,
                                    $request->wing, $request->ward, $request->office)->toArray()));
                                }
                            }
                        }
                    }
    
                }
            }

            //cash incase of other services    
            if((!is_array($request->drugs)) && (!is_array($request->image_test_types))  && (!is_array($request->lab_test_types))){
                $cash_related_prices_array = array_merge($cash_related_prices_array, (ServicePrice::selectFirstExactServicePrice($request->id, $request->service, $request->department, $request->consultation_category, $request->clinic, "cash", null, null,
                $request->consultation_type, $request->visit_type, $request->doctor, $request->current_time, $request->duration, null, null, null, null, $request->branch, $request->building,
                $request->wing, $request->ward, $request->office)->toArray()));
            }

            // insurance incase of other services    
            if((!is_array($request->drugs)) && (!is_array($request->image_test_types))  && (!is_array($request->lab_test_types))){
                $schemes_related_prices_array = array_merge($schemes_related_prices_array, (ServicePrice::selectFirstExactServicePrice($request->id, $request->service, $request->department, $request->consultation_category, $request->clinic, "insurance", null, null,
                        $request->consultation_type, $request->visit_type, $request->doctor, $request->current_time, $request->duration, null, null, null, null, $request->branch, $request->building,
                        $request->wing, $request->ward, $request->office)->toArray()));
            }

            //return $request;

        }
        
        return array_merge($cash_related_prices_array, $schemes_related_prices_array);

    }

}
