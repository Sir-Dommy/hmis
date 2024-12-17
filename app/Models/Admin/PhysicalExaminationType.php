<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalExaminationType extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "physical_examination_types";

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
    public static function selectPhysicalExaminationTypes($id, $name){

        $physical_examination_types_query = PhysicalExaminationType::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('physical_examination_types.deleted_by')
          ->whereNull('physical_examination_types.deleted_at');

        if($id != null){
            $physical_examination_types_query->where('physical_examination_types.id', $id);
        }
        elseif($name != null){
            $physical_examination_types_query->where('physical_examination_types.name', $name);
        }



        return $physical_examination_types_query->get()->map(function ($physical_examination_type) {
            $physical_examination_types_details = [
                'id' => $physical_examination_type->id,
                'name' => $physical_examination_type->name,
                'description' => $physical_examination_type->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($physical_examination_type);

            return array_merge($physical_examination_types_details, $related_user);
        });

    }
}
