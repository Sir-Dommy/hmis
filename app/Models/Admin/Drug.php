<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "drugs";

    protected $fillable = [
        "brand_id",
        "name",
        "in_stock",
        "description",
        "expiry_date",
        "created_by",
        "updated_by",
        "approved_by",
        "approved_at",
        "deleted_by",
        "deleted_at",
    ];

    public function brand(){
        return $this->belongsTo(Brand::class, "brand_id");
    }

    //perform selection
    public static function selectDiagnosis($id, $name){

        $diagnosis_query = Diagnosis::with([
            'brand:id,name',
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('drugs.deleted_by')
          ->whereNull('drugs.deleted_at');

        if($id != null){
            $diagnosis_query->where('drugs.id', $id);
        }
        elseif($name != null){
            $diagnosis_query->where('drugs.name', $name);
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
