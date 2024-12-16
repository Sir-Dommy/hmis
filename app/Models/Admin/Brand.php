<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "brands";

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
    public static function selectBrands($id, $name){

        // return $this->aggregateAllRels();
        $brands_query = Brand::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('brands.deleted_by')
          ->whereNull('brands.deleted_at');

        if($id != null){
            $brands_query->where('brands.id', $id);
        }
        elseif($name != null){
            $brands_query->where('brands.name', $name);
        }



        return $brands_query->get()->map(function ($brand) {
            $brand_details = [
                'id' => $brand->id,
                'name' => $brand->name,
                'description' => $brand->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($brand);

            return array_merge($brand_details, $related_user);
        });

    }
}
