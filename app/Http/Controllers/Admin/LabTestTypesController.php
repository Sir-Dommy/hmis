<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Models\Admin\LabTestType;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;



class LabTestTypesController extends Controller
{
    
     //create
        public function createLabTestType(Request $request){
            $request->validate([
                'name' => 'required|string|min:1|max:255|unique:lab_test_types,name',
                'description'=>'string|min:1|max:255'            
            ]);
    
    
            LabTestType::create([
                'name' => $request->name, 
                'description' => $request->description,
                'created_by' => User::getLoggedInUserId()
            ]);
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a lab test type with name: ". $request->name);
    
            return response()->json(
                LabTestType::selectLabTestTypes(null, $request->name)
            ,200);
    
        }
    
        //update
        public function updateLabTestType(Request $request){
            $request->validate([
                'id' => 'required|integer|exists:lab_test_types,id',
                'name' => 'required|string|min:1|max:255',
                'description'=>'string|min:1|max:255'
                
            ]);
    
            $existing = LabTestType::selectLabTestTypes(null, $request->name);
    
            count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_LAB_TEST_TYPE) : null;
    
    
            LabTestType::where('id', $request->id)
                ->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'updated_at' => Carbon::now(),
                    'updated_by' => User::getLoggedInUserId(),
                    'approved_by' => null,
                    'approved_at' => null
            ]);
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a lab test type  with name: ". $request->name);
    
            return response()->json(
                LabTestType::selectLabTestTypes($request->id, null)
            ,200);
    
        }
    
        //     //Get one 
        public function getSingleLabTestType(Request $request){
    
            $labTestType = LabTestType::selectLabTestTypes($request->id, $request->name);
    
            count($labTestType) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_TYPE) : null ;
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a lab test type with name: ". $labTestType[0]['name']);
    
            return response()->json(
                $labTestType
            ,200);
        }
    
    
        //getting all
        public function getAllLabTestType(){
    
            $labTestType = LabTestType::selectLabTestTypes(null, null);
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all lab test types");
    
            return response()->json(
                $labTestType
            ,200);
        }
    
        //approve
        public function approveLabTestType($id){
    
            count(LabTestType::selectLabTestTypes($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_TYPE) : null;
    
            LabTestType::where('id', $id)
                ->update([
                    'approved_by' => User::getLoggedInUserId(),
                    'approved_at' => Carbon::now()
            ]);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a lab test type with id: ".$id);
    
            return response()->json(
                LabTestType::selectLabTestTypes($id, null)
            ,200);
    
        }
    
        //soft delete
        public function softDeleteLabTestType($id){
                
            count(LabTestType::selectLabTestTypes($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_TYPE) : null;
            
            LabTestType::where('id', $id)
                    ->update([
                        'deleted_at' => now(),
                        'deleted_by' => User::getLoggedInUserId(),
                    ]);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a lab test type with id: ". $id);
    
            return response()->json(
                []
            ,200);
        }
    
        //restore
        public function restoreSoftDeleted($id){ 
            
            count(LabTestType::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_TYPE) : null;
            
            LabTestType::where('id', $id)
                    ->update([
                        'approved_at' => null,
                        'approved_by' => null,
                        'deleted_at' => null,
                        'deleted_by' => null,
                    ]);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored a lab test type with id: ". $id);
    
            return response()->json(
                LabTestType::selectLabTestTypes($id, null)
            ,200);
        }
    
        //permanently delete
        public function permanentlyDelete($id){
                
            count(LabTestType::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_TYPE) : null;
            
            LabTestType::destroy($id);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted a lab test type with id: ". $id);
    
            return response()->json(
                []
            ,200);
        }

}
