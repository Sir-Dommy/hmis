<?php

namespace App\Models\Admin\ServiceRelated;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'services';

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
    public static function selectServices($id, $name){

        $services_query = Service::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('services.deleted_by')
          ->whereNull('services.deleted_at');

        if($id != null){
            $services_query->where('services.id', $id);
        }
        elseif($name != null){
            $services_query->where('services.name', $name);
        }

        // return $schemes_query->get();

        return $services_query->get()->map(function ($service) {
            $service_details = [
                'id' => $service->id,
                'name' => $service->name,
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($service);

            return array_merge($service_details, $related_user);


        });

    }


}
