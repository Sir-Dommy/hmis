<?php

namespace App\Models\Admin;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageTestRequest extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "image_test_request";

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
    public static function selectImageTestRequests($id, $name){

        $image_test_requests_query = ImageTestRequest::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('image_test_requests.deleted_by')
          ->whereNull('image_test_requests.deleted_at');

        if($id != null){
            $image_test_requests_query->where('image_test_requests.id', $id);
        }
        elseif($name != null){
            $image_test_requests_query->where('image_test_requests.name', $name);
        }



        return $image_test_requests_query->get()->map(function ($image_test_request) {
            $image_test_request_details = [
                'id' => $image_test_request->id,
                'name' => $image_test_request->name,
                'description' => $image_test_request->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($image_test_request);

            return array_merge($image_test_request_details, $related_user);
        });

    }
}
