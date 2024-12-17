<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "drugs";

    protected $fillable = [
        "brand_id",
        "name",
        "in_stock",
        "price_per_item",
        "description",
        "expiry_date",
        "created_by",
        "updated_by",
        "approved_by",
        "approved_at",
        "deleted_by",
        "deleted_at",
    ];

    public function brand(){
        return $this->belongsTo(Brand::class, "brand_id");
    }

    //perform selection
    public static function selectDrug($id, $name){

        $drug_query = Diagnosis::with([
            'brand:id,name',
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('drugs.deleted_by')
          ->whereNull('drugs.deleted_at');

        if($id != null){
            $drug_query->where('drugs.id', $id);
        }
        elseif($name != null){
            $drug_query->where('drugs.name', $name);
        }



        return $drug_query->get()->map(function ($drug) {
            $drug_details = [
                'id' => $drug->id,
                'name' => $drug->name,
                'brand' => $drug->brand->name,
                'in_stock' => $drug->in_stock,
                'expiry_date' => $drug->expiry_date,
                'description' => $drug->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($drug);

            return array_merge($drug_details, $related_user);
        });

    }
}
