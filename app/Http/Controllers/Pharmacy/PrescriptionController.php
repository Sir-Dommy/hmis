<?php

namespace App\Http\Controllers\Pharmacy;

use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\ServiceRelated\ServicePrice;
use App\Models\Bill\Bill;
use App\Models\Patient\Visit;
use App\Models\Pharmacy\Prescription;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrescriptionController extends Controller
{
    
    // create prescription
    public function createPrescription(Request $request){
        $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'service_price_details' => 'required|array',
        ]);

        try{
            DB::beginTransaction();

            

            foreach($request->service_price_details as $service_price_detail){

                !is_array($service_price_detail) ? throw new InputsValidationException("Each individual price detail must be of array (object) type!") : null;
                $validator = Validator::make((array) $service_price_detail, [            
                    'id' => 'required',
                    'quantity' => 'required|numeric|min:0',
                    'amount_to_pay' => 'required|numeric|min:0',
                    'discount' => 'nullable|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }

                $existing_service_price_details = ServicePrice::selectFirstExactServicePrice($service_price_detail['id'], null, null, null, null, null, null, null,
                    null, null, null, null, null, null, null, null, null, null, null,
                    null, null, null
                );

                count($existing_service_price_details) < 1 ? throw new NotFoundException("Service price with id: ".$service_price_detail['id']." does not exist!") : null;

                return $service_price_detail['id'];

                Prescription::create([
                    'visit_id' => $request->visit_id,
                    'drug' => $existing_service_price_details[0]['drug'],
                    'drug_formula' => $service_price_detail['drug_formula'],
                    'brand' => $existing_service_price_details[0]['brand'],
                    'dosage_instruction' => $service_price_detail['dosage_instruction'],
                    'prescription_instruction' => $service_price_detail['prescription_instruction'],
                    'status' => APIConstants::STATUS_PENDING,
                ]);

            }

            Bill::createBillAndBillItems($request, $request->visit_id);


            DB::commit();

        }

        catch(Exception $e){

            //rollback transaction
            DB::rollBack();

            throw new Exception($e);
        }
        DB::beginTransaction();
        

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a prescription for patient with visit id: ". $request->visit_id);

        return response()->json(
            Prescription::selectPrescriptions(null, $request->visit_id)
        ,200);

    }

    // test function for looping through visit details to get payment types and schemes
    public function test($visit_id){
        if($visit_id){
            $existing_visit = Visit::selectVisits($visit_id);

            count($existing_visit) < 1 ? throw new InputsValidationException("No visit with id ". $visit_id . " !!!") : null;

            if($existing_visit[0]['payment_types']){
                foreach($existing_visit[0]['payment_types'] as $visit_payment_type){
                    return "ID NI ".$visit_payment_type->paymentType->name ." NA TYPE ID NI ".$visit_payment_type->visit_id;
                    //.$visit_payment_type->payment_type;
                    if($visit_payment_type['name'] == APIConstants::NAME_CASH){
                        $payment_type_to_use = $visit_payment_type['name'];


                        // $service_prices_query->whereHas('paymentType', function ($query) use ($payment_type_to_use) {
                        //     $query->where('name', 'like', "%$payment_type_to_use%");
                        // });
                    }

                    if($visit_payment_type['name'] == APIConstants::NAME_INSURANCE){
                        if($existing_visit[0]['schemes']){
                            foreach($existing_visit[0]['schemes'] as $visit_scheme){
                                $scheme_to_use = $visit_scheme['name'];

                                // select using a scheme
                                // $service_prices_query->whereHas('scheme', function ($query) use ($scheme_to_use) {
                                //     $query->where('name', 'like', "%$scheme_to_use%");
                                // });

                                foreach($visit_scheme->schemeTypes as $visit_scheme_type){
                                    $scheme_type_to_use = $visit_scheme_type['name'];

                                    // select using scheme type
                                    // $service_prices_query->whereHas('schemeType', function ($query) use ($scheme_type_to_use) {
                                    //     $query->where('name', 'like', "%$scheme_type_to_use%");
                                    // });

                                }

                            }
                        }
                    }
                }
            }
        }
    }

    //updating prescription
    public function updatePrescription(Request $request){
        

    }

    //getting single prescription by id
    public function getSinglePrescription($prescription_id){

        $prescription_id == null ? throw new InputsValidationException("Provide a valid prescription id!") : null;

        $prescription = Prescription::selectPrescriptions($prescription_id, null);

        count($prescription) < 1 ? throw new NotFoundException(APIConstants::NAME_PRESCRIPTION) : null;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a prescription with id: ". $prescription[0]['id']);

        return response()->json(
            $prescription
        ,200);
    }


    //getting prescription by visit id
    public function getPrescriptionByVisitId($visit_id){

        $visit_id == null ? throw new InputsValidationException("Provide a valid visit id!") : null;

        $prescription = Prescription::selectPrescriptions(null, $visit_id);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched prescriptions for visit with id: ". $visit_id);

        return response()->json(
            $prescription
        ,200);
    }

    public function getAllPrescriptions(){

        $prescriptions = Prescription::selectPrescriptions(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all prescriptions");


        return response()->json(
            $prescriptions
        ,200);
    }

    public function softDeletePrescription($id){
            
        $existing = Prescription::selectPrescriptions($id, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_PRESCRIPTION. " with id: ". $id);
        }
        
        Prescription::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a prescription with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    public function permanentlyDeletePrescription($id){
            
        
    }
}
