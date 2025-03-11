<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Models\Admin\ImageTestClass;
use App\Models\Admin\ImageTestRequest;
use App\Models\Admin\ImageTestType;
use App\Models\Admin\LabTestClass;
use App\Models\Admin\LabTestRequest;
use App\Models\Admin\LabTestType;
use App\Models\Bill\Bill;
use App\Models\Laboratory\OrderedTests;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderedTestsController extends Controller
{
    //create order test
    public function createOrderTest(Request $request){
        $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'lab_test_type' => 'required|exists:lab_test_types,name',
            'image_test_type' => 'required|exists:image_test_types,name',
            'lab_test_class' => 'required|exists:lab_test_classes,name',
            'image_test_class' => 'required|exists:image_test_classes,name',
            'lab_test_request' => 'nullable|exists:lab_test_types,name',
            'image_test_request' => 'nullable|exists:lab_test_types,name',
            'service_price_details' => 'required|array',
        ]);
        
        $request->lab_test_request ? $lab_request_id = LabTestRequest::where('name', $request->lab_test_request)->get('id')[0]['id'] : $lab_request_id = null;
        
        $request->image_test_request ? $image_request_id = ImageTestRequest::where('name', $request->image_test_request)->get('id')[0]['id'] : $image_request_id = null;

        try{
            DB::beginTransaction();

            $created = OrderedTests::create([
                'visit_id' => $request->visit_id,
                'lab_test_type_id' => LabTestType::where('name', $request->lab_test_type)->get('id')[0]['id'],
                'image_test_type_id' => ImageTestType::where('name', $request->image_test_type)->get('id')[0]['id'],
                'lab_test_class_id' => LabTestClass::where('name', $request->lab_test_class)->get('id')[0]['id'],
                'image_test_class_id' => ImageTestClass::where('name', $request->image_test_class)->get('id')[0]['id'],
                'lab_test_request_id' => $lab_request_id,
                'image_test_request_id' => $image_request_id,
                'clinical_information' => $request->clinical_information,
                'status' => APIConstants::STATUS_PENDING,
                "created_by" => User::getLoggedInUserId(),
            ]);


            //now create bill and its related bill items
            Bill::createBillAndBillItems($request, $request->visit_id);



            DB::commit();

        }

        catch(Exception $e){
            //rollback transaction
            DB::rollBack();

            throw new Exception($e);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Ordered a test with id: ". $created->id);

        return response()->json(
            OrderedTests::selectOrderedTests($created->id)
        ,200);
    }
}
