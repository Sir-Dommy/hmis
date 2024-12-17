<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Models\Admin\ImageTestRequest;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;

class ImageTestRequestsController extends Controller
{
    
    //create
    public function createImageTestRequest(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:image_test_requests,name',
            'description'=>'string|min:1|max:255'            
        ]);


        ImageTestRequest::create([
            'name' => $request->name, 
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created an image test request with name: ". $request->name);

        return response()->json(
            ImageTestRequest::selectImageTestRequests(null, $request->name)
        ,200);

    }

    //update
    public function updateImageTestRequest(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:image_test_requests,id',
            'name' => 'required|string|min:1|max:255',
            'description'=>'string|min:1|max:255'
            
        ]);

        $existing = ImageTestRequest::selectImageTestRequests(null, $request->name);

        count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_IMAGE_TEST_REQUEST) : null;


        ImageTestRequest::where('id', $request->id)
            ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => Carbon::now(),
                'updated_by' => User::getLoggedInUserId(),
                'approved_by' => null,
                'approved_at' => null
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated an image test request with name: ". $request->name);

        return response()->json(
            ImageTestRequest::selectImageTestRequests($request->id, null)
        ,200);

    }

    //     //Get one 
    public function getSingleImageTestRequest(Request $request){

        $imageTestRequest = ImageTestRequest::selectImageTestRequests($request->id, $request->name);

        count($imageTestRequest) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_REQUEST) : null ;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched an image test request with name: ". $imageTestRequest[0]['name']);

        return response()->json(
            $imageTestRequest
        ,200);
    }


    //getting all
    public function getAllImageTestRequest(){

        $imageTestRequest = ImageTestRequest::selectImageTestRequests(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all image test requests");

        return response()->json(
            $imageTestRequest
        ,200);
    }

    //approve
    public function approveImageTestRequest($id){

        count(ImageTestRequest::selectImageTestRequests($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_REQUEST) : null;

        ImageTestRequest::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved an image test request with id: ".$id);

        return response()->json(
            ImageTestRequest::selectImageTestRequests($id, null)
        ,200);

    }

    //soft delete
    public function softDeleteImageTestRequest($id){
            
        count(ImageTestRequest::selectImageTestRequests($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_REQUEST) : null;
        
        ImageTestRequest::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed an image test request with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeleted($id){ 
        
        count(ImageTestRequest::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_REQUEST) : null;
        
        ImageTestRequest::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored an image test request with id: ". $id);

        return response()->json(
            ImageTestRequest::selectImageTestRequests($id, null)
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(ImageTestRequest::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_IMAGE_TEST_REQUEST) : null;
        
        ImageTestRequest::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted an image test request with id: ". $id);

        return response()->json(
            []
        ,200);
    }



}
