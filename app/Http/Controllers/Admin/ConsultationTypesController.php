<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\ConsultationType;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ConsultationTypesController extends Controller
{
    //create
    public function createConsultationType(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:consultation_types,name',
            'description'=>'string|min:1|max:255'            
        ]);


        ConsultationType::create([
            'name' => $request->name, 
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Consultation type with name: ". $request->name);

        return response()->json(
            ConsultationType::selectConsultationTypes(null, $request->name)
        ,200);

    }

    //update
    public function updateConsultationType(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:consultation_types,id',
            'name' => 'required|string|min:1|max:255',
            'description'=>'string|min:1|max:255'
            
        ]);

        $existing = ConsultationType::selectConsultationTypes(null, $request->name);

        count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_CHRONIC_DISEASE) : null;


        ConsultationType::where('id', $request->id)
            ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => Carbon::now(),
                'updated_by' => User::getLoggedInUserId(),
                'approved_by' => null,
                'approved_at' => null
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Consultation type with name: ". $request->name);

        return response()->json(
            ConsultationType::selectConsultationTypes($request->id, null)
        ,200);

    }

    //     //Get one 
    public function getSingleConsultationType(Request $request){

        $consultation_type = ConsultationType::selectConsultationTypes($request->id, $request->name);

        count($consultation_type) < 1 ? throw new NotFoundException(APIConstants::NAME_CONSULTATION_TYPE) : null ;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a Consultation type with name: ". $consultation_type[0]['name']);

        return response()->json(
            $consultation_type
        ,200);
    }


    //getting all
    public function getAllConsultationTypes(){

        $consultation_types = ConsultationType::selectConsultationTypes(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Consultation type");

        return response()->json(
            $consultation_types
        ,200);
    }

    //approve
    public function approveConsultationTypes($id){

        count(ConsultationType::selectConsultationTypes($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_CONSULTATION_TYPE) : null;

        ConsultationType::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved Consultation type with id: ".$id);

        return response()->json(
            ConsultationType::selectConsultationTypes($id, null)
        ,200);

    }

    //soft delete
    public function softDeleteConsultationType($id){
            
        count(ConsultationType::selectConsultationTypes($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_CONSULTATION_TYPE) : null;
        
        ConsultationType::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a Consultation type with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeleted($id){ 
        
        count(ConsultationType::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_CONSULTATION_TYPE) : null;
        
        ConsultationType::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored a Consultation type with id: ". $id);

        return response()->json(
            ConsultationType::selectConsultationTypes($id, null)
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(ConsultationType::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_CONSULTATION_TYPE) : null;
        
        ConsultationType::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted Consultation type with id: ". $id);

        return response()->json(
            []
        ,200);
    }
}
