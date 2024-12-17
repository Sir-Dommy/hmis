<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Models\Admin\ImageTestType;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;


class ImageTestTypesController extends Controller
{
    
        //create
        public function createImageTestType(Request $request){
            $request->validate([
                'name' => 'required|string|min:1|max:255|unique:diagnosis,name',
                'description'=>'string|min:1|max:255'            
            ]);
    
    
            ImageTestType::create([
                'name' => $request->name, 
                'description' => $request->description,
                'created_by' => User::getLoggedInUserId()
            ]);
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created an image test type with name: ". $request->name);
    
            return response()->json(
                ImageTestType::selectImageTestTypes(null, $request->name)
            ,200);
    
        }
    
        //update
        public function updateImageTestType(Request $request){
            $request->validate([
                'id' => 'required|integer|exists:diagnosis,id',
                'name' => 'required|string|min:1|max:255',
                'description'=>'string|min:1|max:255'
                
            ]);
    
            $existing = ImageTestType::selectImageTestTypes(null, $request->name);
    
            count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_IMAGE_TEST_TYPE) : null;
    
    
            ImageTestType::where('id', $request->id)
                ->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'updated_at' => Carbon::now(),
                    'updated_by' => User::getLoggedInUserId(),
                    'approved_by' => null,
                    'approved_at' => null
            ]);
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated an image test type with name: ". $request->name);
    
            return response()->json(
                ImageTestType::selectImageTestTypes($request->id, null)
            ,200);
    
        }
    
        //     //Get one 
        public function getSingleImageTestType(Request $request){
    
            $imageTestType = ImageTestType::selectImageTestTypes($request->id, $request->name);
    
            count($imageTestType) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_TYPE) : null ;
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched an image test type with name: ". $imageTestType[0]['name']);
    
            return response()->json(
                $imageTestType
            ,200);
        }
    
    
        //getting all
        public function getAllImageTestType(){
    
            $imageTestType = ImageTestType::selectImageTestTypes(null, null);
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all image test types");
    
            return response()->json(
                $imageTestType
            ,200);
        }
    
        //approve
        public function approveImageTestType($id){
    
            count(ImageTestType::selectImageTestTypes($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_TYPE) : null;
    
            ImageTestType::where('id', $id)
                ->update([
                    'approved_by' => User::getLoggedInUserId(),
                    'approved_at' => Carbon::now()
            ]);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved an image test type with id: ".$id);
    
            return response()->json(
                ImageTestType::selectImageTestTypes($id, null)
            ,200);
    
        }
    
        //soft delete
        public function softDeleteImageTestType($id){
                
            count(ImageTestType::selectImageTestTypes($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_TYPE) : null;
            
            ImageTestType::where('id', $id)
                    ->update([
                        'deleted_at' => now(),
                        'deleted_by' => User::getLoggedInUserId(),
                    ]);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed an image test type with id: ". $id);
    
            return response()->json(
                []
            ,200);
        }
    
        //restore
        public function restoreSoftDeleted($id){ 
            
            count(ImageTestType::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_TYPE) : null;
            
            ImageTestType::where('id', $id)
                    ->update([
                        'approved_at' => null,
                        'approved_by' => null,
                        'deleted_at' => null,
                        'deleted_by' => null,
                    ]);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored an image test type with id: ". $id);
    
            return response()->json(
                ImageTestType::selectImageTestTypes($id, null)
            ,200);
        }
    
        //permanently delete
        public function permanentlyDelete($id){
                
            count(ImageTestType::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_TYPE) : null;
            
            ImageTestType::destroy($id);
    
    
            UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted an image test type with id: ". $id);
    
            return response()->json(
                []
            ,200);
        }
    

}
