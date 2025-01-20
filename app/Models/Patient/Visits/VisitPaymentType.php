<?php

namespace App\Models\Patient\Visits;

use App\Models\Admin\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitPaymentType extends Model
{
    use HasFactory;

    protected $table =  'visit_patient_payment_types';

    protected $fillable = [
        'visit_id',
        'payment_type_id'
    ];


    //relationship with department
    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'fee_type');
    }
}
