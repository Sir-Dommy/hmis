<?php

namespace App\Models\Doctor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationPhysicalExaminationsJoin extends Model
{
    use HasFactory;

    protected $table = 'consultations_physical_exam_join';

    protected $fillable = [
        'consultation_id',
        'physical_examination_id'
    ];
}
