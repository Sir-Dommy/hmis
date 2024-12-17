<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\Symptom;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SymptomsController extends Controller
{
    //create
    public function createSymptom(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:symptoms,name'           
        ]);


        Symptom::create([
            'name' => $request->name,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a symptom with name: ". $request->name);

        return response()->json(
            Symptom::selectSymptoms(null, $request->name)
        ,200);

    }

    //update
    public function updateSymptom(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:symptoms,id',
            'name' => 'required|string|min:1|max:255',
            
        ]);

        $existing = Symptom::selectSymptoms(null, $request->name);

        count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_SYMPTOM) : null;


        Symptom::where('id', $request->id)
            ->update([
                'name' => $request->name,
                'updated_at' => Carbon::now(),
                'updated_by' => User::getLoggedInUserId(),
                'approved_by' => null,
                'approved_at' => null
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Symptom with name: ". $request->name);

        return response()->json(
            Symptom::selectSymptoms($request->id, null)
        ,200);

    }

    //     //Get one 
    public function getSingleSymptom(Request $request){

        $symptom = Symptom::selectSymptoms($request->id, $request->name);

        count($symptom) < 1 ? throw new NotFoundException(APIConstants::NAME_SYMPTOM) : null ;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a Symptom with name: ". $symptom[0]['name']);

        return response()->json(
            $symptom
        ,200);
    }


    //getting all
    public function getAllSymptom(){

        $symptoms = Symptom::selectSymptoms(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Symptoms");

        return response()->json(
            $symptoms
        ,200);
    }

    //approve
    public function approveSymptom($id){

        count(Symptom::selectSymptoms($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_SYMPTOM) : null;

        Symptom::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved Symptom with id: ".$id);

        return response()->json(
            Symptom::selectSymptoms($id, null)
        ,200);

    }

    //soft delete
    public function softDeleteSymptom($id){
            
        count(Symptom::selectSymptoms($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_SYMPTOM) : null;
        
        Symptom::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a Symptom with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeleted($id){ 
        
        count(Symptom::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_SYMPTOM) : null;
        
        Symptom::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored a Symptom with id: ". $id);

        return response()->json(
            Symptom::selectSymptoms($id, null)
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(Symptom::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_SYMPTOM) : null;
        
        Symptom::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted Symptom with id: ". $id);

        return response()->json(
            []
        ,200);
    }
}
