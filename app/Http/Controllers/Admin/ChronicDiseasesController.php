<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\ChronicDisease;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChronicDiseasesController extends Controller
{
    //create
    public function createChronicDisease(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:chronic_diseases,name',
            'description'=>'string|min:1|max:255'            
        ]);


        ChronicDisease::create([
            'name' => $request->name, 
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Chronic disease with name: ". $request->name);

        return response()->json(
            ChronicDisease::selectChronicDiseases(null, $request->name)
        ,200);

    }

    //update
    public function updateChronicDisease(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:chronic_diseases,id',
            'name' => 'required|string|min:1|max:255',
            'description'=>'string|min:1|max:255'
            
        ]);

        $existing = ChronicDisease::selectChronicDiseases(null, $request->name);

        count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_CHRONIC_DISEASE) : null;


        ChronicDisease::where('id', $request->id)
            ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => Carbon::now(),
                'updated_by' => User::getLoggedInUserId(),
                'approved_by' => null,
                'approved_at' => null
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Chronic disease with name: ". $request->name);

        return response()->json(
            ChronicDisease::selectChronicDiseases($request->id, null)
        ,200);

    }

    //     //Get one 
    public function getSingleChronicDisease(Request $request){

        $chronic_disease = ChronicDisease::selectChronicDiseases($request->id, $request->name);

        count($chronic_disease) < 1 ? throw new NotFoundException(APIConstants::NAME_CHRONIC_DISEASE) : null ;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a Chronic disease with name: ". $chronic_disease[0]['name']);

        return response()->json(
            $chronic_disease
        ,200);
    }


    //getting all
    public function getAllChronicDiseases(){

        $chronic_diseases = ChronicDisease::selectChronicDiseases(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Chronic diseases");

        return response()->json(
            $chronic_diseases
        ,200);
    }

    //approve
    public function approveChronicDisease($id){

        count(ChronicDisease::selectChronicDiseases($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_CHRONIC_DISEASE) : null;

        ChronicDisease::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved Chronic disease with id: ".$id);

        return response()->json(
            ChronicDisease::selectChronicDiseases($id, null)
        ,200);

    }

    //soft delete
    public function softDeleteChronicDisease($id){
            
        count(ChronicDisease::selectChronicDiseases($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_CHRONIC_DISEASE) : null;
        
        ChronicDisease::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a Chronic disease with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeletedChronicDisease($id){ 
        
        count(ChronicDisease::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_CHRONIC_DISEASE) : null;
        
        ChronicDisease::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored a Chronic disease with id: ". $id);

        return response()->json(
            ChronicDisease::selectChronicDiseases($id, null)
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(ChronicDisease::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_CHRONIC_DISEASE) : null;
        
        ChronicDisease::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted Chronic disease with id: ". $id);

        return response()->json(
            []
        ,200);
    }
}
