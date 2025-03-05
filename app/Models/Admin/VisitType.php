<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitType extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'visit_types';

    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at'
    ];

    //perform selection
    public static function selectVisitTypes($id, $name){

        // return $this->aggregateAllRels();
        $visit_types_query = VisitType::with([
            'createdBy:id,email',
            'updatedBy:id,email'
        ])->whereNull('visit_types.deleted_by')
          ->whereNull('visit_types.deleted_at');

        if($id != null){
            $visit_types_query->where('visit_types.id', $id);
        }
        elseif($name != null){
            $visit_types_query->where('visit_types.name', $name);
        }



        return $visit_types_query->get()->map(function ($visit_type) {
            $visit_type_details = [
                'id' => $visit_type->id,
                'name' => $visit_type->name,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($visit_type);

            return array_merge($visit_type_details, $related_user);
        });

    }

    
}
