<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\PhysicalExaminationType;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PhysicalExaminationTypesController extends Controller
{
    //create
    public function createPhysicalExaminationType(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:physical_examination_types,name',
            'description'=>'string|min:1|max:255'            
        ]);


        PhysicalExaminationType::create([
            'name' => $request->name, 
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a physical examination type with name: ". $request->name);

        return response()->json(
            PhysicalExaminationType::selectPhysicalExaminationTypes(null, $request->name)
        ,200);

    }

    //update
    public function updatePhysicalExaminationType(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:physical_examination_types,id',
            'name' => 'required|string|min:1|max:255',
            'description'=>'string|min:1|max:255'
            
        ]);

        $existing = PhysicalExaminationType::selectPhysicalExaminationTypes(null, $request->name);

        count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_PHYSICAL_EXAMINATION_TYPE) : null;


        PhysicalExaminationType::where('id', $request->id)
            ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated' => Carbon::now(),
                'updated_by' => User::getLoggedInUserId(),
                'approved_by' => null,
                'approved_at' => null
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a physical examination types with name: ". $request->name);

        return response()->json(
            PhysicalExaminationType::selectPhysicalExaminationTypes($request->id, null)
        ,200);

    }

    //     //Get one 
    public function getSinglePhysicalExaminationType(Request $request){

        $physical_examination_type = PhysicalExaminationType::selectPhysicalExaminationTypes($request->id, $request->name);

        count($physical_examination_type) < 1 ? throw new NotFoundException(APIConstants::NAME_PHYSICAL_EXAMINATION_TYPE) : null ;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a physical examination type with name: ". $physical_examination_type[0]['name']);

        return response()->json(
            $physical_examination_type
        ,200);
    }


    //getting all
    public function getAllPhysicalExaminationTypes(){

        $physical_examination_types = PhysicalExaminationType::selectPhysicalExaminationTypes(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all physical examination type");

        return response()->json(
            $physical_examination_types
        ,200);
    }

    //approve
    public function approvePhysicalExaminationType($id){

        count(PhysicalExaminationType::selectPhysicalExaminationTypes($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_PHYSICAL_EXAMINATION_TYPE) : null;

        PhysicalExaminationType::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved physical examination type with id: ".$id);

        return response()->json(
            PhysicalExaminationType::selectPhysicalExaminationTypes($id, null)
        ,200);

    }

    //soft delete
    public function softDeletePhysicalExaminationType($id){
            
        count(PhysicalExaminationType::selectPhysicalExaminationTypes($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_PHYSICAL_EXAMINATION_TYPE) : null;
        
        PhysicalExaminationType::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a physical examination type with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeleted($id){ 
        
        count(PhysicalExaminationType::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_PHYSICAL_EXAMINATION_TYPE) : null;
        
        PhysicalExaminationType::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored a physical examination type with id: ". $id);

        return response()->json(
            PhysicalExaminationType::selectPhysicalExaminationTypes($id, null)
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(PhysicalExaminationType::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_PHYSICAL_EXAMINATION_TYPE) : null;
        
        PhysicalExaminationType::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted physical examination type with id: ". $id);

        return response()->json(
            []
        ,200);
    }
}
