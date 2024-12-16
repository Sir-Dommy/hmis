<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageTestType extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "image_test_types";

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
    public static function selectImageTestTypes($id, $name){

        $image_test_types_query = ImageTestType::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('image_test_types.deleted_by')
          ->whereNull('image_test_types.deleted_at');

        if($id != null){
            $image_test_types_query->where('image_test_types.id', $id);
        }
        elseif($name != null){
            $image_test_types_query->where('image_test_types.name', $name);
        }



        return $image_test_types_query->get()->map(function ($image_test_types) {
            $image_test_types_details = [
                'id' => $image_test_types->id,
                'name' => $image_test_types->name,
                'description' => $image_test_types->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($image_test_types);

            return array_merge($image_test_types_details, $related_user);
        });

    }
}
