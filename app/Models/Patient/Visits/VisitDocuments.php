<?php

namespace App\Models\Patient\Visits;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitDocuments extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "visit_documents";

    protected $fillable = [
        "path",
        "description",
        "created_by",
        "updated_by",
        "approved_by",
        "approved_at",
        "deleted_by",
        "deleted_at",
    ];

    //perform selection
    public static function selectVisitDocuments($id){

        $visit_documents_query = VisitDocuments::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('visit_documents.deleted_by')
          ->whereNull('visit_documents.deleted_at');

        if($id != null){
            $visit_documents_query->where('visit_documents.id', $id);
        }

        // split this string "/home/sirdommy/Downloads/wildfly-17.0.1.Final" using "/" as seperator and getlast string

        

        return $visit_documents_query->get()->map(function ($visit_document) {
            $visit_documents_details = [
                'id' => $visit_document->id,
                'name' => end(explode($visit_document->name, "/")),
                'description' => $visit_document->description,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($visit_document);

            return array_merge($visit_documents_details, $related_user);
        });

    }
}
