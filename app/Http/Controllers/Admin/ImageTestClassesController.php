<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Models\Admin\ImageTestClass;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ImageTestClassesController extends Controller
{
    
    //create
    public function createImageTestClass(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:image_test_classes,name',
            'description'=>'string|min:1|max:255'            
        ]);


        ImageTestClass::create([
            'name' => $request->name, 
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created an ImageTest class with name: ". $request->name);

        return response()->json(
            ImageTestClass::selectImageTestClass(null, $request->name)
        ,200);

    }

    //update
    public function updateImageTestClass(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:image_test_classes,id',
            'name' => 'required|string|min:1|max:255',
            'description'=>'string|min:1|max:255'
            
        ]);

        $existing = ImageTestClass::selectImageTestClass(null, $request->name);

        count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_IMAGE_TEST_CLASS) : null;


        ImageTestClass::where('id', $request->id)
            ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => Carbon::now(),
                'updated_by' => User::getLoggedInUserId(),
                'approved_by' => null,
                'approved_at' => null
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated an image Test Class with name: ". $request->name);

        return response()->json(
            ImageTestClass::selectImageTestClass($request->id, null)
        ,200);

    }

    //     //Get one image test .class
    public function getSingleImageTestClass(Request $request){

        $ImageTestClass = ImageTestClass::selectImageTestClass($request->id, $request->name);

        count($ImageTestClass) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_CLASS) : null ;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched an ImageTestClass with name: ". $ImageTestClass[0]['name']);

        return response()->json(
            $ImageTestClass
        ,200);
    }


    //getting all
    public function getAllImageTestClass(){

        $imageTestClass = ImageTestClass::selectImageTestClass(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all ImageTestClasses");

        return response()->json(
            $imageTestClass
        ,200);
    }

    //approve
    public function approveImageTestClass($id){

        count(ImageTestClass::selectImageTestClass($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_CLASS) : null;

        ImageTestClass::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved an image test class with id: ".$id);

        return response()->json(
            ImageTestClass::selectImageTestClass($id, null)
        ,200);

    }

    //soft delete
    public function softDeleteImageTestClass($id){
            
        count(ImageTestClass::selectImageTestClass($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_CLASS) : null;
        
        ImageTestClass::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed an image test class with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeleted($id){ 
        
        count(ImageTestClass::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_CLASS) : null;
        
        ImageTestClass::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored an image test class with id: ". $id);

        return response()->json(
            ImageTestClass::selectImageTestClass($id, null)
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(ImageTestClass::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_CLASS) : null;
        
        ImageTestClass::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted an image test class with id: ". $id);

        return response()->json(
            []
        ,200);
    }


}
