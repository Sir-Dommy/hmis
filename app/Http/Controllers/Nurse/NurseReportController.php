<?php

namespace App\Http\Controllers\Nurse;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Nurse\NurseReport;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Illuminate\Http\Request;

class NurseReportController extends Controller
{
    //create
    public function createNurseReport(Request $request){
        $request->validate([
            'visit_id' => 'required|integer|exists:visits,id',
            'report' => 'required|string|min:1|max:2550'          
        ]);


        $created = NurseReport::create([
            'visit_id' => $request->visit_id,
            'report' => $request->report,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a nurse report for visit with id: ". $request->visit_id);

        return response()->json(
            NurseReport::selectNurseReports($created->id, null)
        ,200);

    }

    //update
    public function updateNurseReport(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:nurse_reports,id',
            'visit_id' => 'required|integer|exists:visits,id',
            'report' => 'required|string|min:1|max:2550'          
        ]);


        NurseReport::where('id', $request->id)
        ->update([
            'visit_id' => $request->visit_id,
            'report' => $request->report,
            'updated_by' => User::getLoggedInUserId()
        ]);
        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a nurse report with id: ". $request->id);

        
        return response()->json(
            NurseReport::selectNurseReports($request->id, null)
        ,200);
    }

    //     //Get one 
    public function getNurseReports(Request $request){

        $nurse_report = NurseReport::selectNurseReports($request->id, $request->visit_id);

        count($nurse_report) < 1 ? throw new NotFoundException(APIConstants::NAME_NURSE_REPORT) : null;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Nurse report using details: id: ". $request->id ." visit_id: ". $request->visit_id);

        return response()->json(
            $nurse_report
        ,200);
    }


    //getting all
    public function getAllNurseReports(){

        $nurse_reports = NurseReport::selectNurseReports(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all nurse reports");

        return response()->json(
            $nurse_reports
        ,200);
    }

    //soft delete
    public function softDelete($id){
            
        count(NurseReport::selectNurseReports($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_NURSE_REPORT) : null;
        
        NurseReport::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a nurse report with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeleted($id){ 
        
        count(NurseReport::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_NURSE_REPORT) : null;
        
        NurseReport::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored a nurse report with id: ". $id);

        return response()->json(
            NurseReport::selectNurseReports($id, null)
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(NurseReport::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_NURSE_REPORT) : null;
        
        NurseReport::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted nurse report with id: ". $id);

        return response()->json(
            []
        ,200);
    }

}
