<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "payment_types";

    protected $fillable = [
        "name",
        "description",
        "created_by",
        "updated_by",
        "approved_by",
        "approved_at",
        "deleted_by",
        "deleted_at",
    ];


    //perform selection
    public static function selectPaymentTypes($id, $name){

        // return $this->aggregateAllRels();
        $payment_type_query = PaymentType::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('payment_types.deleted_by')
          ->whereNull('payment_types.deleted_at');

        if($id != null){
            $payment_type_query->where('payment_types.id', $id);
        }
        elseif($name != null){
            $payment_type_query->where('payment_types.name', $name);
        }



        return $payment_type_query->get()->map(function ($payment_type) {
            $payment_type_details = [
                'id' => $payment_type->id,
                'name' => $payment_type->name,
                'description' => $payment_type->description,
                
                // 'created_by' => $scheme->createdBy ? $scheme->createdBy->email : null,
                // 'created_at' => $scheme->created_at,
                // 'updated_by' => $scheme->updatedBy ? $scheme->updatedBy->email : null,
                // 'updated_at' => $scheme->updated_at,
                // 'approved_by' => $scheme->approvedBy ? $scheme->approvedBy->email : null,
                // 'approved_at' => $scheme->approved_at,
                // 'disabled_by' => $scheme->disabledBy ? $scheme->disabledBy->email : null,
                // 'disabled_at' => $scheme->disabled_at,
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($payment_type);

            return array_merge($payment_type_details, $related_user);
        });

    }
}
