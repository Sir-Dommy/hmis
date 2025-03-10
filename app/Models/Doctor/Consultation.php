<?php

namespace App\Models\Doctor;

use App\Models\Admin\ConsultationType;
use App\Models\Admin\Diagnosis;
use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'consultations';

    protected $fillable = [
        "visit_id",
        "consultation_type_id",
        "clinical_history",
        "created_by",
        "end_time",
        "updated_by",
        "deleted_by",
        "deleted_at",
    ];

    public function consultationType(){
        return $this->belongsTo(ConsultationType::class, 'consultation_type_id');
    }

    public function diagnosis(){
        return $this->belongsToMany(Diagnosis::class, 'consultation_diagnosis_join', 'consultation_id', 'diagnosis_id');
    }

    public function symptoms(){
        return $this->belongsToMany(Diagnosis::class, 'consultations_symptoms_join', 'consultation_id', 'symptom_id');
    }

    public function physicalExaminations(){
        return $this->belongsToMany(Diagnosis::class, 'consultations_physical_exam_join', 'consultation_id', 'physical_examination_id');
    }


    //perform selection
    public static function selectConsultations($id){

        $consultations_query = Consultation::with([
            'consultationType:id,name',
            'diagnosis:id,name',
            'symptoms:id,name',
            'physicalExaminations:id,name',
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('consultations.deleted_by')
          ->whereNull('consultations.deleted_at');

        if($id != null){
            $consultations_query->where('consultations.id', $id);
        }



        return $consultations_query->get()->map(function ($consultation) {
            $consultations_details = [
                'id' => $consultation->id,
                'visit_id' => $consultation->visit_id,
                'consultation_type' => $consultation->consultationType->name,
                'clinical_history' => $consultation->clinical_history,
                'diagnosis' => $consultation->diagnosis,
                'symptoms' => $consultation->symptoms,
                'physical_examinations' => $consultation->physicalExaminations,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($consultation);

            return array_merge($consultations_details, $related_user);
        });

    }

}
