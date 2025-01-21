<?php

namespace App\Models\Bill;

use App\Models\Admin\Scheme;
use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'transactions';

    protected $fillable = [
        'bill_id',
        'patient_account_no',
        'hospital_account_no',
        'scheme_name',
        'scheme_id',
        'initiation_time',
        'amount',
        'fee',
        'receipt_date',
        'status',
        'is_reversed',
        'reverse_date',
        'reason',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'deleted_by',
        'deleted_at'
    ];

    public function bill()
    {
        return $this->belongsTo(BillItem::class, 'bill_id');
    }
    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }
}
