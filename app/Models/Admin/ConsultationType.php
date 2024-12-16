<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationType extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "consultation_types";

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
    public static function selectConsultationTypes($id, $name){

        $consultation_types_query = ConsultationType::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('consultation_types.deleted_by')
          ->whereNull('consultation_types.deleted_at');

        if($id != null){
            $consultation_types_query->where('consultation_types.id', $id);
        }
        elseif($name != null){
            $consultation_types_query->where('consultation_types.name', $name);
        }



        return $consultation_types_query->get()->map(function ($consultation_type) {
            $consultation_types_details = [
                'id' => $consultation_type->id,
                'name' => $consultation_type->name,
                'description' => $consultation_type->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($consultation_type);

            return array_merge($consultation_types_details, $related_user);
        });

    }
}
