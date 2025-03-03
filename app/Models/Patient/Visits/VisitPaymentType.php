<?php

namespace App\Models\Patient\Visits;

use App\Models\Admin\PaymentType;
use App\Models\Admin\ServiceRelated\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitPaymentType extends Model
{
    use HasFactory;

    protected $table =  'visit_patient_payment_types';

    protected $fillable = [
        'visit_id',
        'payment_type_id',
        'insurer_name',
        'scheme_type_name',
        'service_name',
        'service_price',
        'amount_to_pay'
    ];


    //relationship with payment type
    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'fee_type');
    }

}
