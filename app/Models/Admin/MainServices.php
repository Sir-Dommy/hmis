<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainServices extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "main_services";

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
    public static function selectMainServices($id, $name){

        $main_services_query = MainServices::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('main_services.deleted_by')
          ->whereNull('main_services.deleted_at');

        if($id != null){
            $main_services_query->where('main_services.id', $id);
        }
        elseif($name != null){
            $main_services_query->where('main_services.name', $name);
        }



        return $main_services_query->get()->map(function ($main_service) {
            $main_services_details = [
                'id' => $main_service->id,
                'name' => $main_service->name,
                'description' => $main_service->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($main_service);

            return array_merge($main_services_details, $related_user);
        });

    }
}
