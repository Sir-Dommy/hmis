<?php

namespace App\Http\Controllers\Patient;
use App\Models\Patient\Vital;
use App\Exceptions\NotFoundException;
use App\Models\Admin\Scheme;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class VitalController extends Controller
{
    
    public function createVital(Request $request){
        $request->validate([
            'visits_id' => 'required|exists:visits,id',
            'weight' => 'numeric|between:2,255',
            'blood_pressure' => 'required|regex:/^\d{2,3}\/\d{2,3}$/', // for diastolic or systolic values 
            'blood_glucose' => 'numeric|regex:/^\d+(\.\d{1,2})?$/|between:70,500', // allow value up to 2 decimal places between 70 and 500
            'height' => 'required|numeric|min:50|max:300',
            'blood_type' => 'required|string|min:1',
            'disease' => 'string|min:3|max:25',
            'allergies' => 'string|min:2|max:255|exists:roles,name',
            'nursing_remarks' => 'string|min:3|max:25'
        ]);

        Vital::create([
            'weight' => $request->weight, 
            'blood_pressure'=>$request->blood_pressure,
            'blood_glucose'=>$request->blood_glucose, 
            'height' => $request->height,
            'blood_type' => $request->blood_type,
            'disease'=>$request->disease,
            'allergies' => $request->allergies,
            'nursing_remarks' => $request->nursing_remarks,
            'visits_id'=> $request->visits_id,
            'created_by' => Auth::user()->id
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a vital for patient with visit id: ". $request->visits_id);

        return response()->json(
            Vital::selectVitals(null, $request->visits_id, null)
        ,200);

    }

//updating vital details
    public function updateVital(Request $request){
        $request->validate([
            'id'=>'required|exists:vitals,id',
            'visits_id' => 'required|exists:visits,id',
            'weight' => 'numeric|between:2,255',
            'blood_pressure' => 'required|regex:/^\d{2,3}\/\d{2,3}$/', // for diastolic or systolic values 
            'blood_glucose' => 'numeric|regex:/^\d+(\.\d{1,2})?$/|between:70,500', // allow value up to 2 decimal places between 70 and 500
            'height' => 'required|numeric|min:50|max:300',
            'blood_type' => 'required|string|min:1',
            'disease' => 'string|min:3|max:25',
            'allergies' => 'string|min:2|max:255|exists:roles,name',
            'nursing_remarks' => 'string|min:3|max:25'
        ]);

        $existing = Vital::selectVitals($request->id,$request->visits_id);

        if(!$existing){
            throw new NotFoundException(APIConstants::NAME_VITAL);
        }

        Vital::where('id', $request->id)
                ->update([
                     'visits_id' =>$request->visits_id ,
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

        if($request->id == null){
            throw new InputsValidationException("vital id is required!");
        }

        $vital = Vital::selectVitals($request->id, null);

        if(count($vital) < 1){
            throw new NotFoundException(APIConstants::NAME_VITAL);
        }

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