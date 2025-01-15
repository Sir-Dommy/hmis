<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestType extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "lab_test_types";

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
    public static function selectLabTestTypes($id, $name){

        $lab_test_types_query = LabTestType::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('lab_test_types.deleted_by')
          ->whereNull('lab_test_types.deleted_at');

        if($id != null){
            $lab_test_types_query->where('lab_test_types.id', $id);
        }
        elseif($name != null){
            $lab_test_types_query->where('lab_test_types.name', $name);
        }



        return $lab_test_types_query->get()->map(function ($lab_test_type) {
            $lab_test_type_details = [
                'id' => $lab_test_type->id,
                'name' => $lab_test_type->name,
                'description' => $lab_test_type->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($lab_test_type);

            return array_merge($lab_test_type_details, $related_user);
        });

    }
}
