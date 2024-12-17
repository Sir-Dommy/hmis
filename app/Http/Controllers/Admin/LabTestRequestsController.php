<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Models\Admin\LabTestRequest;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;


class LabTestRequestsController extends Controller
{

    //create
    public function createLabTestRequest(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:lab_test_request,name',
            'description'=>'string|min:1|max:255'            
        ]);


        LabTestRequest::create([
            'name' => $request->name, 
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);
        
        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a lab test request with name: ". $request->name);

        return response()->json(
            LabTestRequest::selectLabTestRequests(null, $request->name)
        ,200);

    }

    //update
    public function updateLabTestRequest(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:lab_test_request,id',
            'name' => 'required|string|min:1|max:255',
            'description'=>'string|min:1|max:255'
            
        ]);

        $existing = LabTestRequest::selectLabTestRequests(null, $request->name);

        count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_LAB_TEST_REQUEST) : null;


        LabTestRequest::where('id', $request->id)
            ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => Carbon::now(),
                'updated_by' => User::getLoggedInUserId(),
                'approved_by' => null,
                'approved_at' => null
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated lab test request with name: ". $request->name);

        return response()->json(
            LabTestRequest::selectLabTestRequests($request->id, null)
        ,200);

    }

    //     //Get one 
    public function getSingleLabTestRequest(Request $request){

        $labTestRequest = LabTestRequest::selectLabTestRequests($request->id, $request->name);

        count($labTestRequest) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_REQUEST) : null ;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a lab test request with name: ". $labTestRequest[0]['name']);

        return response()->json(
            $labTestRequest
        ,200);
    }


    //getting all
    public function getAllLabTestRequest(){

        $labTestRequest = LabTestRequest::selectLabTestRequests(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all lab test requests");

        return response()->json(
            $labTestRequest
        ,200);
    }

    //approve
    public function approveLabTestRequest($id){

        count(LabTestRequest::selectLabTestRequests($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_REQUEST) : null;

        LabTestRequest::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a lab test request with id: ".$id);

        return response()->json(
            LabTestRequest::selectLabTestRequests($id, null)
        ,200);

    }

    //soft delete
    public function softDeleteLabTestRequest($id){
            
        count(LabTestRequest::selectLabTestRequests($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_REQUEST) : null;
        
        LabTestRequest::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a lab test request with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeleted($id){ 
        
        count(LabTestRequest::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_REQUEST) : null;
        
        LabTestRequest::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored a Lab test request with id: ". $id);

        return response()->json(
            LabTestRequest::selectLabTestRequests($id, null)
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(LabTestRequest::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_LAB_TEST_REQUEST) : null;
        
        LabTestRequest::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted a Lab test request with id: ". $id);

        return response()->json(
            []
        ,200);
    }

}
