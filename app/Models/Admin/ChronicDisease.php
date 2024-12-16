<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChronicDisease extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "chronic_diseases";

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
    public static function selectChronicDiseases($id, $name){

        // return $this->aggregateAllRels();
        $chronic_disease_query = ChronicDisease::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('chronic_diseases.deleted_by')
          ->whereNull('chronic_diseases.deleted_at');

        if($id != null){
            $chronic_disease_query->where('chronic_diseases.id', $id);
        }
        elseif($name != null){
            $chronic_disease_query->where('chronic_diseases.name', $name);
        }



        return $chronic_disease_query->get()->map(function ($chronic_disease) {
            $chronic_disease_details = [
                'id' => $chronic_disease->id,
                'name' => $chronic_disease->name,
                'description' => $chronic_disease->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($chronic_disease);

            return array_merge($chronic_disease_details, $related_user);
        });

    }
}
