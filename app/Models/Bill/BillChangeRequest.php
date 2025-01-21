<?php

namespace App\Models\Bill;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillChangeRequest extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'bill_change_requests';

    protected $fillable = [
        'bill_id',
        'initial_bill_amount',
        'update_bill_amount',
        'initial_discount',
        'update_discount',
        'status',
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
        return $this->belongsTo(Bill::class, 'bill_id');
    }
}
