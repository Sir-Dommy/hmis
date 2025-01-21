<?php

namespace App\Models\Bill;

use App\Models\Admin\Scheme;
use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionChangeRequest extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'transaction_change_requests';

    protected $fillable = [
        'transaction_id',
        'initial_patient_account_no',
        'update_patient_account_no',
        'initial_hospital_account_no',
        'update_hospital_account_no',
        'initial_scheme_name',
        'update_scheme_name',
        'initial_scheme_id',
        'update_scheme_id',
        'initial_amount',
        'update_amount',
        'initial_fee',
        'update_fee',
        'initial_status',
        'update_status',
        'initial_reason',
        'update_reason',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'deleted_by',
        'deleted_at'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function initialScheme()
    {
        return $this->belongsTo(Scheme::class, 'initial_scheme_id');
    }

    public function updateScheme()
    {
        return $this->belongsTo(Scheme::class, 'update_scheme_id');
    }
}
