<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientPaymentTypesJoin extends Model
{
    use HasFactory;

    protected $table = 'patients_payment_methods_join';

    protected $fillable = [
        'patient_id',
        'payment_type_id'
    ];
}
