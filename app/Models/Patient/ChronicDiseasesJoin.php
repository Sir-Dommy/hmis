<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChronicDiseasesJoin extends Model
{
    use HasFactory;

    protected $table = 'patients_chronic_diseases_join';

    protected $fillable = [
        'patient_id',
        'chronic_disease_id'
    ];
}
