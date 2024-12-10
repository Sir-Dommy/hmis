<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "clinics";

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
    public static function selectClinics($id, $name){

        // return $this->aggregateAllRels();
        $sclinics_query = Clinic::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('clinics.deleted_by')
          ->whereNull('clinics.deleted_at');

        if($id != null){
            $sclinics_query->where('clinics.id', $id);
        }
        elseif($name != null){
            $sclinics_query->where('clinics.name', $name);
        }



        return $sclinics_query->get()->map(function ($clinic) {
            $clinic_details = [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'description' => $clinic->description,
                
                // 'created_by' => $scheme->createdBy ? $scheme->createdBy->email : null,
                // 'created_at' => $scheme->created_at,
                // 'updated_by' => $scheme->updatedBy ? $scheme->updatedBy->email : null,
                // 'updated_at' => $scheme->updated_at,
                // 'approved_by' => $scheme->approvedBy ? $scheme->approvedBy->email : null,
                // 'approved_at' => $scheme->approved_at,
                // 'disabled_by' => $scheme->disabledBy ? $scheme->disabledBy->email : null,
                // 'disabled_at' => $scheme->disabled_at,
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($clinic);

            return array_merge($clinic_details, $related_user);
        });

    }
}
