<?php

namespace App\Models\Patient\Visits;

use App\Models\Admin\Scheme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitInsuranceDetail extends Model
{
    use HasFactory;

    protected $table = 'visit_patient_insurance_details';

    protected $fillable = [
        'visit_id',
        'claim_number',
        'available_balance',
        'scheme_id',
        'document_path'
    ];


    //relationship with payment Type
    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }
}
