<?php

namespace App\Models\Laboratory;

use App\Models\Admin\ImageTestClass;
use App\Models\Admin\ImageTestRequest;
use App\Models\Admin\ImageTestType;
use App\Models\Admin\LabTestClass;
use App\Models\Admin\LabTestRequest;
use App\Models\Admin\LabTestType;
use App\Models\Patient\Visit;
use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderedTests extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'ordered_tests';

    protected $fillable = [
        'visit_id',
        'lab_test_type_id',
        'image_test_type_id',
        'lab_test_class_id',
        'image_test_class_id',
        'lab_test_request_id',
        'image_test_request_id',
        'clinical_information',
        'status',
        "created_by",
        "updated_by",
        "deleted_by",
        "deleted_at"
    ];

    public function visit(){
        return $this->belongsTo(Visit::class, 'visit_id');
    }

    public function labTestType(){
        return $this->belongsTo(LabTestType::class, 'lab_test_type_id');
    }

    public function imageTestType(){
        return $this->belongsTo(ImageTestType::class, 'image_test_type_id');
    }

    public function labTestClass(){
        return $this->belongsTo(LabTestClass::class, 'lab_test_class_id');
    }

    public function imageTestClass(){
        return $this->belongsTo(ImageTestClass::class, 'image_test_class_id');
    }

    public function labTestRequest(){
        return $this->belongsTo(LabTestRequest::class, 'lab_test_request_id');
    }

    public function imageTestRequest(){
        return $this->belongsTo(ImageTestRequest::class, 'image_test_request_id');
    }


    //perform selection
    public static function selectOrderedTests($id){

        $ordered_tests_query = OrderedTests::with([
            'visit:id,patient_id',
            'labTestType:id,name',
            'imageTestType:id,name',
            'labTestClass:id,name',
            'imageTestClass:id,name',
            'labTestRequest:id,name',
            'imageTestRequest:id,name',
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('ordered_tests.deleted_by')
          ->whereNull('ordered_tests.deleted_at');

        if($id != null){
            $ordered_tests_query->where('ordered_tests.id', $id);
        }



        return $ordered_tests_query->get()->map(function ($ordered_test) {
            $ordered_test_details = [
                'id' => $ordered_test->id,
                'visit_id' => $ordered_test->visit->id,
                'patient_id' => $ordered_test->visit->patient_id,
                'lab_test_type' => $ordered_test->labTestType->name,
                'image_test_type' => $ordered_test->imageTestType->name,
                'lab_test_class' => $ordered_test->labTestClass->name,
                'image_test_class' => $ordered_test->imageTestClass->name,
                'lab_test_request' => $ordered_test->labTestRequest ? $ordered_test->labTestRequest->name : null,
                'image_test_request' => $ordered_test->imageTestRequest ? $ordered_test->imageTestRequest->name : null,
                'clinical_information' => $ordered_test->clinical_information,
                'status' => $ordered_test->status
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($ordered_test);

            return array_merge($ordered_test_details, $related_user);
        });

    }


}
