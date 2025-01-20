<?php

namespace App\Http\Controllers\Patient;

use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\Clinic;
use App\Models\Admin\Department;
use App\Models\Admin\PaymentType;
use App\Models\Admin\Scheme;
use App\Models\Admin\VisitType;
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
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'department' => 'required|string|exists:departments,name',
            'clinic'=>'required|string|exists:clinics,name',
            'visit_type'=>'required|string|exists:visit_types,name',
            'schemes' => 'nullable',
            'payment_types'=>'required',
        
        ]);
        

        $department = Department::where('name', $request->department)->get("id");
        $clinic = Clinic::where('name', $request->clinic)->get("id");
        $visit_type = VisitType::selectVisitTypes(null, $request->visit_type);

        // return response()->file($path);
        // echo $department[0]['id'];

        DB::beginTransaction();

        try{
            $visit = Visit::create([
                'patient_id' => $request->patient_id, 
                'visit_type_id' => $visit_type[0]['id'],
                'stage'=> 0,
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

    
            $this->validateAndSaveVisitPaymentType($request->payment_types, $visit->id, $request->schemes);
    
            foreach($request->schemes as $scheme){
                Validator::make((array) $scheme, [
                    'claim_number' => 'required|string',
                    'available_balance' => 'required|numeric',
                    'insurer' => 'required|string|exists:schemes,name',
                ]);

                $existing_scheme = Scheme::where('name', $scheme->insurer)->get("id");

                VisitInsuranceDetail::create([
                    'visit_id' => $visit->id,
                    'claim_number' => $scheme->claim_number,
                    'available_balance' => $scheme->claim_number,
                    'scheme_id' => $existing_scheme[0]['id']
                ]);
            }

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

    public function updateVisit(Request $request){
        $request->validate([
            'id' => 'required|exists:visits,id',
            'patient_id' => 'required|exists:patients,id',
            'claim_number' => 'required|string|min:1|max:255',
            'amount'=>'required|numeric|min:0|',
            'department' => 'required|string|exists:departments,name',
            'clinic'=>'required|string|exists:clinics,name',
            'visit_type'=>'required|string|min:1|max:255',
            'scheme' => 'required|string|exists:schemes,name',
            'fee_type'=>'required|string|exists:payment_types,name',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Allowed formats and max size 2MB
        
        ]);

        //store image first 
        // Get the uploaded file
        $path = null;
        if($request->file('image')){
            $image = $request->file('image');

            // Generate a new unique name for the image
            $newName = uniqid() . '.' . $image->getClientOriginalExtension();
    
            // Store the image in the public folder
            $path = $image->move(public_path('images/claims'), $newName);
        }
        

        $department = Department::where('name', $request->department)->get("id");
        $clinic = Clinic::where('name', $request->clinic)->get("id");
        $scheme = Scheme::where('name', $request->scheme)->get("id");
        $fee_type = PaymentType::where('name', $request->fee_type)->get("id");

        // return response()->file($path);
        // echo $department[0]['id'];
        Visit::where('id', $request->id)
            ->update([
                'patient_id' => $request->patient_id,
                'claim_number' => $request->claim_number, 
                'amount' => $request->amount,
                'department_id'=>$department[0]['id'],
                'clinic_id'=>$clinic[0]['id'], 
                'visit_type' => $request->visit_type,
                'scheme_id' => $scheme[0]['id'],
                'fee_type'=>$fee_type[0]['id'],
                'stage'=>0,
                'document_path'=>$path,
                'created_by' => User::getLoggedInUserId()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a visit with id: ". $request->id);

        return response()->json(
            Visit::selectVisits($request->id)
        ,200);

    }

//     //Getting a single visit 
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
        var_dump($payment_types);
        foreach($payment_types as $payment_type){

            var_dump($payment_type);

            if($payment_type->cash == true){
                $payment_method = "Cash";
            }
    
            else if($payment_type->insurance == true){
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

}
