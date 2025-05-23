<?php

namespace App\Models\Admin;

use App\Models\PaymentPath;
use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scheme extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "schemes";

    protected $fillable = [
        "payment_type_id",
        "name",
        "account",
        "initiate_url",
        "bill_url",
        "authentication_url",
        "validation_url",
        "balance_url",
        "bridge_balance_url",
        "other_url",
        "username",
        "password",
        "other_details",
        "description",
        "payment_path_id",
        "created_by",
        "updated_by",
        "approved_by",
        "approved_at",
        "disabled_by",
        "disabled_at",
        "deleted_by",
        "deleted_at",
    ];

    //relationship with schemes_types
    public function schemeTypes()
    {
        return $this->hasMany(SchemeTypes::class, 'scheme_id', 'id');
    }

    //relationship with Payment types
    public function paymentTypes()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    //relationship with payment Paths
    public function paymentPath()
    {
        return $this->belongsTo(PaymentPath::class, 'payment_path_id');
    }


    //perform selection
    public static function selectSchemes($id, $name){

        // return $this->aggregateAllRels();
        $schemes_query = Scheme::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email',
            'schemeTypes:id,scheme_id,name,max_visits_per_year,max_amount_per_visit',
            'paymentTypes:id,name',
            'paymentPath:id,name'
        ])->whereNull('schemes.deleted_by')
          ->whereNull('schemes.deleted_at');

        if($id != null){
            $schemes_query->where('schemes.id', $id);
        }
        elseif($name != null){
            $schemes_query->where('schemes.name', $name);
        }

        // return $schemes_query->get();

        return $schemes_query->get()->map(function ($scheme) {
            $scheme_details = [
                'id' => $scheme->id,
                'name' => $scheme->name,
                'account' => $scheme->account,
                'initiate_url' => $scheme->initiate_url,
                'authentication_url' => $scheme->authentication_url,
                'validation_url' => $scheme->validation_url,
                'balance_url' => $scheme->balance_url,
                'bridge_balance_url' => $scheme->bridge_balance_url,
                'other_url' => $scheme->other_url,
                'username' => $scheme->username,
                'password' => $scheme->password,
                'other_details' => $scheme->other_details,
                'description' => $scheme->description,
                'schemeTypes' => $scheme->schemeTypes,
                'payment_type_id' => $scheme->paymentTypes->id,
                'payment_type_name' => $scheme->paymentTypes->name,
                'payment_path' => $scheme->paymentPath->name,
                
                // 'created_by' => $scheme->createdBy ? $scheme->createdBy->email : null,
                // 'created_at' => $scheme->created_at,
                // 'updated_by' => $scheme->updatedBy ? $scheme->updatedBy->email : null,
                // 'updated_at' => $scheme->updated_at,
                // 'approved_by' => $scheme->approvedBy ? $scheme->approvedBy->email : null,
                // 'approved_at' => $scheme->approved_at,
                // 'disabled_by' => $scheme->disabledBy ? $scheme->disabledBy->email : null,
                // 'disabled_at' => $scheme->disabled_at,
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($scheme);

            return array_merge($scheme_details, $related_user);
        });
    }



}
