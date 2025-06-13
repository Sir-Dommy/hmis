<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor\Admission;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Illuminate\Http\Request;

class AdmissionsController extends Controller
{
    //create admission
    public function createAdmission(Request $request){
        $request->validate([
            'visit_id' => 'required|exists:visits,id|unique:admissions,visit_id',
            'expected_length_of_stay' => 'required|numeric|min:0'
        ]);

        $created = Admission::create([
            "admission_code" => $this->generateAdmissionCode(),
            "visit_id" => $request->visit_id,
            "chief_complains" => $request->chief_complains,
            "present_illness_history" => $request->present_illness_history,
            "past_medical_history" => $request->past_medical_history,
            "expected_length_of_stay" => $request->expected_length_of_stay,
            "reason_for_admission" => $request->reason_for_admission,
            "created_by" => auth()->user()->id,
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Admission with id: ". $created->id);

        return response()->json(
            Admission::selectAdmission($created->id, null, null, null),
            200
        );
    }

    //update admission
    public function updateAdmission(Request $request){
        $request->validate([
            'id' => 'required|exists:admissions,id',
            'visit_id' => 'required|exists:visits,id|unique:admissions,visit_id',
            'expected_length_of_stay' => 'required|numeric|min:0'
        ]);

        Admission::where('id',  $request->id)->update([
            "admission_code" => $this->generateAdmissionCode(),
            "visit_id" => $request->visit_id,
            "chief_complains" => $request->chief_complains,
            "present_illness_history" => $request->present_illness_history,
            "past_medical_history" => $request->past_medical_history,
            "expected_length_of_stay" => $request->expected_length_of_stay,
            "reason_for_admission" => $request->reason_for_admission,
            "update_by" => auth()->user()->id,
        ]);

        $updated = Admission::selectAdmission($request->id, null, null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Admission with id: ". $request->id);

        return response()->json(
            $updated,
            200
        );
    }

    public function getSingleAdmission($id){        
        $admission = Admission::selectAdmission($id, null, null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Get a Admission with id: ". $id);

        return response()->json(
            $admission,
            200
        );
    }

    public function getAdmissions(){        
        $admissions = Admission::selectAdmission(null, null, null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Get all Admissions with: ");

        return response()->json(
            $admissions,
            200
        );
    }

    private function generateAdmissionCode(){
        $admission_code = "AD-" . date('Ymd') . "-" . rand(1000, 99999999);

        if(Admission::where('admission_code', $admission_code)->first()){
            $this->generateAdmissionCode();
        }

        return $admission_code;
    }
}
