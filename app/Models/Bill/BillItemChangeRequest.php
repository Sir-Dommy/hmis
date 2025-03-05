<?php

namespace App\Models\Bill;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItemChangeRequest extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'bill_item_change_requests';

    protected $fillable = [
        'bill_item_id',
        'initial_amount',
        'update_amount',
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

    public function billItem()
    {
        return $this->belongsTo(BillItem::class, 'bill_item_id');
    }
}
