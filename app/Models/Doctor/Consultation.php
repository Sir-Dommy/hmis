<?php

namespace App\Models\Doctor;

use App\Models\Admin\Diagnosis;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

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

    public function diagnosis(){
        return $this->belongsToMany(Diagnosis::class, 'consultation_diagnosis_join', 'consultation_id', 'diagnosis_id');
    }

}
