<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\Diagnosis;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DiagnosisController extends Controller
{
    //create
    public function createDiagnosis(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:diagnosis,name',
            'description'=>'string|min:1|max:255'            
        ]);


        Diagnosis::create([
            'name' => $request->name, 
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Diagnosis with name: ". $request->name);

        return response()->json(
            Diagnosis::selectDiagnosis(null, $request->name)
        ,200);

    }

    //update
    public function updateDiagnosis(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:diagnosis,id',
            'name' => 'required|string|min:1|max:255',
            'description'=>'string|min:1|max:255'
            
        ]);

        $existing = Diagnosis::selectDiagnosis(null, $request->name);

        count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_DIAGNOSIS) : null;


        Diagnosis::where('id', $request->id)
            ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => Carbon::now(),
                'updated_by' => User::getLoggedInUserId(),
                'approved_by' => null,
                'approved_at' => null
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Diagnosis with name: ". $request->name);

        return response()->json(
            Diagnosis::selectDiagnosis($request->id, null)
        ,200);

    }

    //     //Get one 
    public function getSingleDiagnosis(Request $request){

        $diagnosis = Diagnosis::selectDiagnosis($request->id, $request->name);

        count($diagnosis) < 1 ? throw new NotFoundException(APIConstants::NAME_DIAGNOSIS) : null ;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a diagnosis with name: ". $diagnosis[0]['name']);

        return response()->json(
            $diagnosis
        ,200);
    }


    //getting all
    public function getAllDiagnosis(){

        $diagnosis = Diagnosis::selectDiagnosis(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all diagnosis");

        return response()->json(
            $diagnosis
        ,200);
    }

    //approve
    public function approveDiagnosis($id){

        count(Diagnosis::selectDiagnosis($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_DIAGNOSIS) : null;

        Diagnosis::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a diagnosis with id: ".$id);

        return response()->json(
            Diagnosis::selectDiagnosis($id, null)
        ,200);

    }

    //soft delete
    public function softDeleteDiagnosis($id){
            
        count(Diagnosis::selectDiagnosis($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_DIAGNOSIS) : null;
        
        Diagnosis::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a Diagnosis with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeleted($id){ 
        
        count(Diagnosis::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_DIAGNOSIS) : null;
        
        Diagnosis::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored a diagnosis with id: ". $id);

        return response()->json(
            Diagnosis::selectDiagnosis($id, null)
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(Diagnosis::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_DIAGNOSIS) : null;
        
        Diagnosis::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted a diagnosis with id: ". $id);

        return response()->json(
            []
        ,200);
    }
}
