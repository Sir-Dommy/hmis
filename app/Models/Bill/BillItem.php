<?php

namespace App\Models\Bill;

use App\Models\Admin\ServiceRelated\ServicePrice;
use App\Models\User;
use App\Utils\APIConstants;
use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'bill_items';

    protected $fillable = [
        'bill_id',
        'service_item_id',
        'item_movement_id',
        'unit',
        'one_item_selling_price',
        'discount',
        'quantity',
        'amount_paid',
        'description',
        'status',
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

    public function serviceItem()
    {
        return $this->belongsTo(ServicePrice::class, 'service_item_id');
    }

    //perform selection
    public static function selectBillItems($id){
        $bill_items_query = Bill::with([
            'bill:id,bill_reference_number',
            'serviceItem:id,price,duration',
            'serviceItem.service:id,name',
            'serviceItem.doctor:id,ipnumber'
        ])->whereNull('bill_items.deleted_by');

        if($id != null){
            $bill_items_query->where('bill_items.id', $id);
        }


        else{
            $paginated_bill_items = $bill_items_query->paginate(10);

            //return $bills;
            $paginated_bill_items->getCollection()->transform(function ($bill_item) {
                return BillItem::mapResponse($bill_item);
            });
    
            return $paginated_bill_items;
        }


        return $bill_items_query->get()->map(function ($bill_item) {
            $bill_item_details = Bill::mapResponse($bill_item);

            return $bill_item_details;
        });


    }

    private static function mapResponse($bill_item){
        return [
            'id' => $bill_item->id,
            'bill_id' => $bill_item->bill_id,
            'bill_reference_number'=>$bill_item->bill->bill_reference_number,
            'service_details' => $bill_item->serviceItem,
            'unit' => $bill_item->serviceItem,
            'one_item_selling_price' => $bill_item->one_item_selling_price,
            'discount' => $bill_item->discount,
            'quantity' => $bill_item->quantity,
            'amount_paid' => $bill_item->amount_paid,
            'description' => $bill_item->description,
            'status' => $bill_item->status,
            'created_by' => $bill_item->createdBy ? $bill_item->createdBy->email : null,
            'created_at' => $bill_item->created_at,
            'updated_by' => $bill_item->updatedBy ? $bill_item->updatedBy->email : null,
            'updated_at' => $bill_item->updated_at,
            'approved_by' => $bill_item->approvedBy ? $bill_item->approvedBy->email : null,
            'approved_at' => $bill_item->approved_at,    

        ];
    }

    public static function createBillItem($bill_id, $service_item_id, $item_movement_id, $unit_id, $one_item_selling_price, $discount, $quantity, $description){
        BillItem::create([
            'bill_id' => $bill_id,
            'service_item_id' => $service_item_id,
            'item_movement_id' => $item_movement_id,
            'unit_id' => $unit_id,
            'one_item_selling_price' => $one_item_selling_price,
            'total_amount' => $one_item_selling_price * $quantity,
            'discount' => $discount,
            'quantity' => $quantity,
            'description' => $description,
            'status' => APIConstants::STATUS_PENDING,
            'created_by' => User::getLoggedInUserId()
        ]);
    }


}
