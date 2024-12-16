<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugFormula extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "drug_formulations";

    protected $fillable = [
        "name",
        "formula",
        "description",
        "created_by",
        "updated_by",
        "approved_by",
        "approved_at",
        "deleted_by",
        "deleted_at",
    ];

    //perform selection
    public static function selectDrugFormulation($id, $name){

        $drug_formulations_query = DrugFormula::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('drug_formulations.deleted_by')
          ->whereNull('drug_formulations.deleted_at');

        if($id != null){
            $drug_formulations_query->where('drug_formulations.id', $id);
        }
        elseif($name != null){
            $drug_formulations_query->where('drug_formulations.name', $name);
        }



        return $drug_formulations_query->get()->map(function ($drug_formulation) {
            $drug_formulation_details = [
                'id' => $drug_formulation->id,
                'name' => $drug_formulation->name,
                'description' => $drug_formulation->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($drug_formulation);

            return array_merge($drug_formulation_details, $related_user);
        });

    }
}
