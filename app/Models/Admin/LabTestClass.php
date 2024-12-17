<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestClass extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "lab_test_classes";

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
    public static function selectLabTestClass($id, $name){

        $lab_test_classes_query = LabTestClass::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('lab_test_classes.deleted_by')
          ->whereNull('lab_test_classes.deleted_at');

        if($id != null){
            $lab_test_classes_query->where('lab_test_classes.id', $id);
        }
        elseif($name != null){
            $lab_test_classes_query->where('lab_test_classes.name', $name);
        }



        return $lab_test_classes_query->get()->map(function ($lab_test_classes) {
            $lab_test_classes_details = [
                'id' => $lab_test_classes->id,
                'name' => $lab_test_classes->name,
                'description' => $lab_test_classes->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($lab_test_classes);

            return array_merge($lab_test_classes_details, $related_user);
        });

    }
}
