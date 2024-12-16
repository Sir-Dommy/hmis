<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "diagnosis";

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
    public static function selectDiagnosis($id, $name){

        $diagnosis_query = Diagnosis::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('diagnosis.deleted_by')
          ->whereNull('diagnosis.deleted_at');

        if($id != null){
            $diagnosis_query->where('diagnosis.id', $id);
        }
        elseif($name != null){
            $diagnosis_query->where('diagnosis.name', $name);
        }



        return $diagnosis_query->get()->map(function ($diagnosis) {
            $diagnosis_details = [
                'id' => $diagnosis->id,
                'name' => $diagnosis->name,
                'description' => $diagnosis->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($diagnosis);

            return array_merge($diagnosis_details, $related_user);
        });

    }
}
