<?php

namespace App\Models\Patient;

use App\Models\Admin\Scheme;
use App\Models\Admin\SchemeTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceDetail extends Model
{
    use HasFactory;

    protected $table = "insurance_details";

    protected $fillable = [
        'patient_id',
        'insurer_id',
        'scheme_type_id',
        'mobile_number',
        'insurance_card_path',
        'principal_member_name',
        'principal_member_number',
        'member_validity',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function schemes()
    {
        return $this->hasMany(Scheme::class, 'insurer_id', 'id');
    }

    public function schemeTypes()
    {
        return $this->hasMany(SchemeTypes::class, 'scheme_type_id', 'id');
    }
}
