<?php

namespace App\Models\Doctor;

use App\Models\Patient\Visit;
use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'admissions';

    protected $fillable = [
        "chief_complains",
        "present_illness_history",
        "past_medical_history",
        "expected_length_of_stay",
        "reason_for_admission",
        "created_by",
        "created_at",
        "updated_by",
        "updated_at",
        "approved_by",
        "approved_at",
        "deleted_by",
        "deleted_at",
        "admission_code",
        "visit_id"
    ];

    public function visit(){
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }

    public static function selectAdmission($id, $admission_code, $visit_id, $patient_reference){

        $admissions_query = Admission::with(['visit']);
        
        if($id != null){
            $admissions_query->where('admissions.id', $id);
        }
        elseif($admission_code != null){
            $admissions_query->where('admissions.admission_code', $admission_code);
        }
        elseif($visit_id != null){
            $admissions_query->where('admissions.visit_id', $visit_id);
        }
        // elseif($patient_reference != null){
        //     $admissions_query->where('admissions.id_no', $id_no);
        // }


        else{
            $paginated_admissions = $admissions_query->paginate(10);
            //return $paginated_patients;
            $paginated_admissions->getCollection()->transform(function ($admission) {
                return Admission::mapResponse($admission);
            });
    
            return $paginated_admissions;
        }


        return $admissions_query->get()->map(function ($admission) {
            $admission_details = Admission::mapResponse($admission);

            return $admission_details;
        });
    }

    private static function mapResponse($admission){
        return [
            "id" => $admission->id,
            "chief_complains" => $admission->chief_complains,
            "present_illness_history" => $admission->present_illness_history,
            "past_medical_history" => $admission->past_medical_history,
            "expected_length_of_stay" => $admission->expected_length_of_stay,
            "reason_for_admission" => $admission->reason_for_admission,
            
            'visits' => $admission->visits,
            'created_by' => $admission->createdBy ? $admission->createdBy->email : null,
            'created_at' => $admission->created_at,
            'updated_by' => $admission->updatedBy ? $admission->updatedBy->email : null,
            'updated_at' => $admission->updated_at,
            'approved_by' => $admission->approvedBy ? $admission->approvedBy->email : null,
            'approved_at' => $admission->approved_at, 

            
        ];
    }

    
}
