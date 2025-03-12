<?php

namespace App\Http\Controllers\Pharmacy;

use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Bill\Bill;
use App\Models\Pharmacy\Prescription;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    
    // create prescription
    public function createPrescription(Request $request){
        $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'drug' => 'required|exists:drugs,name',
            'service_price_details' => 'required|array',
        ]);

        try{
            DB::beginTransaction();

            

            foreach($request->service_price_details as $service_price_detail){

                !is_array($service_price_detail) ? throw new InputsValidationException("Each individual price detail must be of array (object) type!") : null;

                return $service_price_detail['id'];

                Prescription::create([
                    'visit_id' => $request->visit_id,
                    'drug' => $request->drug,
                    'drug_formula' => $request->drug_formula,
                    'brand' => $request->brand,
                    'dosage_instruction' => $request->dosage_instruction,
                    'prescription_instruction' => $request->prescription_instruction,
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
