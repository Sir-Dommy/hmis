<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageTestClass extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "image_test_classes";

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
    public static function selectImageTestClass($id, $name){

        $image_test_classes_query = ImageTestClass::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('image_test_classes.deleted_by')
          ->whereNull('image_test_classes.deleted_at');

        if($id != null){
            $image_test_classes_query->where('image_test_classes.id', $id);
        }
        elseif($name != null){
            $image_test_classes_query->where('image_test_classes.name', $name);
        }



        return $image_test_classes_query->get()->map(function ($image_test_class) {
            $image_test_class_details = [
                'id' => $image_test_class->id,
                'name' => $image_test_class->name,
                'description' => $image_test_class->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($image_test_class);

            return array_merge($image_test_class_details, $related_user);
        });

    }
}
