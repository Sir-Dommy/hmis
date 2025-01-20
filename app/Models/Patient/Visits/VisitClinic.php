<?php

namespace App\Models\Patient\Visits;

use App\Models\Admin\Clinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitClinic extends Model
{
    use HasFactory;

    protected $table = 'visit_patient_clinics';

    protected $fillable = [
        'visit_id',
        'clinic_id',
        'time_in',
        'time_out'
    ];


    //relationship with clinic
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
}
