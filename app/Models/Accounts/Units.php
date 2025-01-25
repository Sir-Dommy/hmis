<?php

namespace App\Models\Accounts;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Units extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'units';

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'deleted_by',
        'deleted_at'
    ];


    //perform selection
    public static function selectUnits($id, $name){

        // return $this->aggregateAllRels();
        $units_query = SubAccounts::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('units.deleted_by')
          ->whereNull('units.deleted_at');

        if($id != null){
            $units_query->where('units.id', $id);
        }
        elseif($name != null){
            $units_query->where('units.name', $name);
        }

        // return $schemes_query->get();

        return $units_query->get()->map(function ($unit) {
            $unit_details = [
                'id' => $unit->id,
                'name' => $unit->name,
                'description' => $unit->description,

            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($unit);

            return array_merge($unit_details, $related_user);
        });

    }
}
