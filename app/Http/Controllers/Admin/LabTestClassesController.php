<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Models\Admin\LabTestClass;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;



class LabTestClassesController extends Controller
{

        //create
        public function createLabTestClass(Request $request){
            $request->validate([
                'name' => 'required|string|min:1|max:255|unique:lab_test_classes,name',
                'description'=>'string|min:1|max:255'            
            ]);
    
    
            LabTestClass::create([
                'name' => $request->name, 
                'description' => $request->description,
                'created_by' => User::getLoggedInUserId()
            ]);
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a lab test class with name: ". $request->name);
    
            return response()->json(
                LabTestClass::selectLabTestClass(null, $request->name)
            ,200);
    
        }
    
        //update
        public function updateLabTestClass(Request $request){
            $request->validate([
                'id' => 'required|integer|exists:lab_test_classes,id',
                'name' => 'required|string|min:1|max:255',
                'description'=>'string|min:1|max:255'
                
            ]);
    
            $existing = LabTestClass::selectLabTestClass(null, $request->name);
    
            count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_LAB_TEST_CLASS) : null;
    
    
            LabTestClass::where('id', $request->id)
                ->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'updated_at' => Carbon::now(),
                    'updated_by' => User::getLoggedInUserId(),
                    'approved_by' => null,
                    'approved_at' => null
            ]);
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a lab test class with name: ". $request->name);
    
            return response()->json(
                LabTestClass::selectLabTestClass($request->id, null)
            ,200);
    
        }
    
        //     //Get one 
        public function getSingleLabTestClass(Request $request){
    
            $labTestClass = LabTestClass::selectLabTestClass($request->id, $request->name);
    
            count($labTestClass) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_CLASS) : null ;
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a lab test class with name: ". $labTestClass[0]['name']);
    
            return response()->json(
                $labTestClass
            ,200);
        }
    
    
        //getting all
        public function getAllLabTestClass(){
    
            $labTestClass = LabTestClass::selectLabTestClass(null, null);
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all lab test classes");
    
            return response()->json(
                $labTestClass
            ,200);
        }
    
        //approve
        public function approveLabTestClass($id){
    
            count(LabTestClass::selectLabTestClass($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_CLASS) : null;
    
            LabTestClass::where('id', $id)
                ->update([
                    'approved_by' => User::getLoggedInUserId(),
                    'approved_at' => Carbon::now()
            ]);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a lab test class with id: ".$id);
    
            return response()->json(
                LabTestClass::selectLabTestClass($id, null)
            ,200);
    
        }
    
        //soft delete
        public function softDeleteLabTestClass($id){
                
            count(LabTestClass::selectLabTestClass($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_CLASS) : null;
            
            LabTestClass::where('id', $id)
                    ->update([
                        'deleted_at' => now(),
                        'deleted_by' => User::getLoggedInUserId(),
                    ]);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a lab test class with id: ". $id);
    
            return response()->json(
                []
            ,200);
        }
    
        //restore
        public function restoreSoftDeleted($id){ 
            
            count(LabTestClass::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_CLASS) : null;
            
            LabTestClass::where('id', $id)
                    ->update([
                        'approved_at' => null,
                        'approved_by' => null,
                        'deleted_at' => null,
                        'deleted_by' => null,
                    ]);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored a lab test class with id: ". $id);
    
            return response()->json(
                LabTestClass::selectLabTestClass($id, null)
            ,200);
        }
    
        //permanently delete
        public function permanentlyDelete($id){
                
            count(LabTestClass::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_CLASS) : null;
            
            LabTestClass::destroy($id);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted a lab test class with id: ". $id);
    
            return response()->json(
                []
            ,200);
        }


}
