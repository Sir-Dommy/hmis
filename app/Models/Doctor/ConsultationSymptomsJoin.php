<?php

namespace App\Models\Doctor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationSymptomsJoin extends Model
{
    use HasFactory;

    protected $table = 'consultations_symptoms_join';

    protected $fillable = [
        'consultation_id',
        'symptom_id'
    ];
}
