<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestRequest extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "lab_test_requests";

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
    public static function selectLabTestRequests($id, $name){

        $lab_test_requests_query = LabTestRequest::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('lab_test_requests.deleted_by')
          ->whereNull('lab_test_requests.deleted_at');

        if($id != null){
            $lab_test_requests_query->where('lab_test_requests.id', $id);
        }
        elseif($name != null){
            $lab_test_requests_query->where('lab_test_requests.name', $name);
        }



        return $lab_test_requests_query->get()->map(function ($lab_test_requests) {
            $lab_test_requests_details = [
                'id' => $lab_test_requests->id,
                'name' => $lab_test_requests->name,
                'description' => $lab_test_requests->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($lab_test_requests);

            return array_merge($lab_test_requests_details, $related_user);
        });

    }
}
