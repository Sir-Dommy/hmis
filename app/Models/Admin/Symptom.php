<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Symptom extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "symptoms";

    protected $fillable = [
        "name",
        "created_by",
        "updated_by",
        "approved_by",
        "approved_at",
        "deleted_by",
        "deleted_at",
    ];

    //perform selection
    public static function selectSymptoms($id, $name){

        $symptoms_query = Symptom::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('symptoms.deleted_by')
          ->whereNull('symptoms.deleted_at');

        if($id != null){
            $symptoms_query->where('symptoms.id', $id);
        }
        elseif($name != null){
            $symptoms_query->where('symptoms.name', $name);
        }



        return $symptoms_query->get()->map(function ($symptom) {
            $symptom_details = [
                'id' => $symptom->id,
                'name' => $symptom->name,
                'description' => $symptom->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($symptom);

            return array_merge($symptom_details, $related_user);
        });

    }
}
