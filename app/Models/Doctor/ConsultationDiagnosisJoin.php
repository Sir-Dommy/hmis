<?php

namespace App\Models\Doctor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationDiagnosisJoin extends Model
{
    use HasFactory;

    protected $table = 'consultations_diagnosis_join';

    protected $fillable = [
    'consultation_id',
    'diagnosis_id',
    ]
}
