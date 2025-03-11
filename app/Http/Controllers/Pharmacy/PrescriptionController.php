<?php

namespace App\Http\Controllers\Pharmacy;

use App\Exceptions\InputsValidationException;
use App\Http\Controllers\Controller;
use App\Models\Bill\Bill;
use App\Models\Pharmacy\Prescription;
use App\Utils\APIConstants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    
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

        Vital::create([
            'weight' => $request->weight, 
            'blood_pressure'=>$request->blood_pressure,
            'blood_glucose'=>$request->blood_glucose, 
            'height' => $request->height,
            'blood_type' => $request->blood_type,
            'disease'=>$request->disease,
            'allergies' => $request->allergies,
            'nursing_remarks' => $request->nursing_remarks,
            'visit_id'=> $request->visit_id,
            'created_by' => Auth::user()->id
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a vital for patient with visit id: ". $request->visit_id);

        return response()->json(
            Vital::selectVitals(null, $request->visit_id, null)
        ,200);

    }

//updating vital details
    public function updateVital(Request $request){
        $request->validate([
            'id'=>'required|exists:vitals,id',
            'visit_id' => 'required|exists:visits,id',
            'weight' => 'numeric|between:2,255',
            'blood_pressure' => 'required|regex:/^\d{2,3}\/\d{2,3}$/', // for diastolic or systolic values 
            'blood_glucose' => 'numeric|regex:/^\d+(\.\d{1,2})?$/|between:70,500', // allow value up to 2 decimal places between 70 and 500
            'height' => 'required|numeric|min:50|max:300',
            'blood_type' => 'required|string|min:1',
            'disease' => 'string|min:3|max:25',
            'allergies' => 'string|min:2|max:255',
            'nursing_remarks' => 'string|min:3|max:25'
        ]);

        $existing = Vital::selectVitals($request->id,$request->visits_id);

        if(!$existing){
            throw new NotFoundException(APIConstants::NAME_VITAL);
        }

        Vital::where('id', $request->id)
                ->update([
                     'weight' =>$request->weight ,
                     'blood_pressure' =>$request->blood_pressure, 
                     'blood_glucose' => $request->blood_glucose, 
                     'height' =>$request->height,
                     'blood_type' => $request->blood_type,
                     'disease' =>$request->disease ,
                     'allergies' =>$request->allergies ,
                     'nursing_remarks' =>$request->nursing_remarks ,
                     'updated_by' => User::getLoggedInUserId()
                ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a vital with id: ". $request->id);
        

        return response()->json(
            Vital::selectVitals($request->id, null)
        ,200);

    }

    //getting single vital
    public function getSingleVital(Request $request){

        ($request->vital_id == null && $request->visit_id == null) ? throw new InputsValidationException("vital id or visit id is required!") : null;

        $vital = Vital::selectVitals($request->vital_id, $request->visit_id);

        count($vital) < 1 ? throw new NotFoundException(APIConstants::NAME_VITAL) : null;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a vital with id: ". $vital[0]['id']);

        return response()->json(
            $vital
        ,200);
    }

    public function getAllVitals(){

        $vitals = Vital::selectVitals(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Vitals");


        return response()->json(
            $vitals
        ,200);
    }

    public function softDeleteVital($id){
            
        $existing = Vital::selectVitals($id, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_VITAL. " with id: ". $id);
        }
        
        Vital::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a vital with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    public function permanentlyDeleteVital($id){
            
        $existing = Vital::where("id",$id)->get();

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_VITAL. " with id: ". $id);
        }
        
        Vital::destroy($id);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted a vital with id: ". $id);

        return response()->json(
            []
        ,200);
    }
}
