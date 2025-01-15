<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\Clinic;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    //saving a new Clinic
    public function createClinic(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:clinics',
            'description'=>'string|min:2|max:255'
            
        ]);        

        Clinic::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a clinic with name: ". $request->name);

        return response()->json(
            Clinic::selectClinics(null, $request->name)
        ,200);

    }

    // updating a clinic
    public function updateClinic(Request $request){
        $request->validate([
            'id' => 'required|integer|min:0|exists:clinics,id',
            'name' => 'required|string|min:1|max:255',
            'description' => 'string|min:1|max:255' 
        ]);

        $existing = Clinic::selectClinics(null, $request->name);

        if(count($existing) > 0 && $existing[0]["id"] != $request->id){
            throw new AlreadyExistsException(APIConstants::NAME_CLINIC. " ". $request->email);
        }
        

        Clinic::where('id', $request->id)
                ->update([
                    'name' => $request->name, 
                    'description' => $request->description,
                    'updated_by' => User::getLoggedInUserId()
                ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Clinic with id: ". $request->id);
        

        return response()->json(
            Clinic::selectClinics($request->id, null)
            ,200);

    }
    //Gettind a single clinic
    public function getSingleClinic(Request $request){

        if($request->id == null && $request->name == null){
            throw new InputsValidationException("id or name required!");
        }

        $clinic = Clinic::selectClinics($request->id, $request->name);

        if(count($clinic) < 1){
            throw new NotFoundException(APIConstants::NAME_CLINIC);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a clinic with id: ". $clinic[0]['id']);

        return response()->json(
            $clinic
        ,200);
    }
    //getting all patients Details
    public function getAllClinics(){

        $clinics = Clinic::selectClinics(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all clinics");


        return response()->json(
            $clinics
        ,200);
    }

    //approving a patient
    public function approveClinic($id){
            
        $existing = Clinic::selectClinics($id, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_CLINIC. " with id: ". $id);
        }

        Clinic::where('id', $id)
                ->update([
                    'approved_by' => User::getLoggedInUserId(),  
                    'approved_at' => Carbon::now(),
                ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a clinic with id : ". $id);

        return response()->json(
            Clinic::selectClinics($id, null)
        ,200);
    }

    public function softDelete($id){
            
        $existing = Clinic::selectClinics($id, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_CLINIC. " with id: ". $id);
        }
        
        Clinic::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a clinic with name: ". $existing[0]['name']);

        return response()->json(
            Clinic::selectClinics($id, null)
        ,200);
    }

    public function permanentlyDelete($id){
            
        $existing = Clinic::where('id',$id)->get();

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_CLINIC. " with id: ". $id);
        }
        
        Clinic::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted a clinic with name: ". $existing[0]['name']);

        return response()->json(
            []
        ,200);
    }

}
