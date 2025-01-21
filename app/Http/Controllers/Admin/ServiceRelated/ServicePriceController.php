<?php

namespace App\Http\Controllers\Admin\ServiceRelated;

use App\Http\Controllers\Controller;
use App\Models\Admin\Brand;
use App\Models\Admin\Clinic;
use App\Models\Admin\ConsultationType;
use App\Models\Admin\Department;
use App\Models\Admin\Drug;
use App\Models\Admin\Employee;
use App\Models\Admin\ImageTestType;
use App\Models\Admin\LabTestType;
use App\Models\Admin\PaymentType;
use App\Models\Admin\Scheme;
use App\Models\Admin\SchemeTypes;
use App\Models\Admin\ServiceRelated\Building;
use App\Models\Admin\ServiceRelated\ConsultationCategory;
use App\Models\Admin\ServiceRelated\Office;
use App\Models\Admin\ServiceRelated\Service;
use App\Models\Admin\ServiceRelated\ServicePrice;
use App\Models\Admin\ServiceRelated\Ward;
use App\Models\Admin\ServiceRelated\Wing;
use App\Models\Admin\VisitType;
use App\Models\Branch;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Illuminate\Http\Request;

class ServicePriceController extends Controller
{
    //get all services
    public function getAllServices(){        

        $all = ServicePrice::selectServicePrice(null, null, null, null, null, null, null, null,
        null, null, null, null, null, null, null, null, null, null, null,
        null, null, null
        );

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Service Prices");

        return response()->json(
                $all ,200);
    }

    //get a single service
    public function getSingleService($id){   
        
        $all = ServicePrice::selectServicePrice($id, null, null, null, null, null, null, null,
        null, null, null, null, null, null, null, null, null, null, null,
        null, null, null
        );

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched Service Price with id: ".$all[0]['id']);

        return response()->json(
                $all ,200);
    }
    
    //create a service
    public function createService(Request $request){
        $request->validate([
            'service' => 'nullable|exists:services,name',
            'department' => 'nullable|exists:departments,name',
            'consultation_category' => 'nullable|exists:consultation_categories,name',
            'clinic' => 'nullable|exists:clinics,name',
            'payment_type' => 'nullable|exists:payment_types,name',
            'scheme' => 'nullable|exists:schemes,name',
            'scheme_type' => 'nullable|exists:scheme_types,name',
            'consultation_type' => 'nullable|exists:consultation_types,name',
            'visit_type' => 'nullable|exists:visit_types,name',
            'doctor' => 'nullable|string', // Assuming doctor is an employee
            'lab_test_type' => 'nullable|exists:lab_test_types,name',
            'image_test_type' => 'nullable|exists:image_test_types,name',
            'drug' => 'nullable|exists:drugs,name',
            'brand' => 'nullable|exists:brands,name',
            'branch' => 'nullable|exists:branches,name',
            'building' => 'nullable|exists:buildings,name',
            'wing' => 'nullable|exists:wings,name',
            'ward' => 'nullable|exists:wards,name',
            'office' => 'nullable|exists:offices,name',
            'price' => 'nullable|numeric', // Price should be numeric
            'price_applies_from' => 'nullable|date_format:H:i', // Valid time in 24-hour format
            'price_applies_to' => 'nullable|date_format:H:i', // Valid time in 24-hour format
            'duration' => 'nullable|numeric|regex:/^\d+(\.\d{1,4})?$/'
        ]);

        // Initialize variables for each entity
        $service_id = $department_id = $consultation_category_id = $clinic_id = null;
        $payment_type_id = $scheme_id = $scheme_type_id = $consultation_type_id = null;
        $visit_type_id = $doctor_id = $lab_test_type_id = $image_test_type_id = null;
        $drug_id = $brand_id = $branch_id = $building_id = $wing_id = null;
        $ward_id = $office_id = null;

        // Perform individual queries and assign the ids or null
        if ($request->has('service')) {
            $service = Service::where('name', $request->service)->first();
            $service_id = $service ? $service->id : null;
        }

        if ($request->has('department')) {
            $department = Department::where('name', $request->department)->first();
            $department_id = $department ? $department->id : null;
        }

        if ($request->has('consultation_category')) {
            $consultationCategory = ConsultationCategory::where('name', $request->consultation_category)->first();
            $consultation_category_id = $consultationCategory ? $consultationCategory->id : null;
        }

        if ($request->has('clinic')) {
            $clinic = Clinic::where('name', $request->clinic)->first();
            $clinic_id = $clinic ? $clinic->id : null;
        }

        if ($request->has('payment_type')) {
            $paymentType = PaymentType::where('name', $request->payment_type)->first();
            $payment_type_id = $paymentType ? $paymentType->id : null;
        }

        if ($request->has('scheme')) {
            $scheme = Scheme::where('name', $request->scheme)->first();
            $scheme_id = $scheme ? $scheme->id : null;
        }

        if ($request->has('scheme_type')) {
            $schemeType = SchemeTypes::where('name', $request->scheme_type)->first();
            $scheme_type_id = $schemeType ? $schemeType->id : null;
        }

        if ($request->has('consultation_type')) {
            $consultationType = ConsultationType::where('name', $request->consultation_type)->first();
            $consultation_type_id = $consultationType ? $consultationType->id : null;
        }

        if ($request->has('visit_type')) {
            $visitType = VisitType::where('name', $request->visit_type)->first();
            $visit_type_id = $visitType ? $visitType->id : null;
        }

        if ($request->has('doctor')) {
            $doctor = Employee::where('employee_code', $request->doctor)->first(); // Assuming employees are doctors
            $doctor_id = $doctor ? $doctor->id : null;
        }

        if ($request->has('lab_test_type')) {
            $labTestType = LabTestType::where('name', $request->lab_test_type)->first();
            $lab_test_type_id = $labTestType ? $labTestType->id : null;
        }

        if ($request->has('image_test_type')) {
            $imageTestType = ImageTestType::where('name', $request->image_test_type)->first();
            $image_test_type_id = $imageTestType ? $imageTestType->id : null;
        }

        if ($request->has('drug')) {
            $drug = Drug::where('name', $request->drug)->first();
            $drug_id = $drug ? $drug->id : null;
        }

        if ($request->has('brand')) {
            $brand = Brand::where('name', $request->brand)->first();
            $brand_id = $brand ? $brand->id : null;
        }

        if ($request->has('branch')) {
            $branch = Branch::where('name', $request->branch)->first();
            $branch_id = $branch ? $branch->id : null;
        }

        if ($request->has('building')) {
            $building = Building::where('name', $request->building)->first();
            $building_id = $building ? $building->id : null;
        }

        if ($request->has('wing')) {
            $wing = Wing::where('name', $request->wing)->first();
            $wing_id = $wing ? $wing->id : null;
        }

        if ($request->has('ward')) {
            $ward = Ward::where('name', $request->ward)->first();
            $ward_id = $ward ? $ward->id : null;
        }

        if ($request->has('office')) {
            $office = Office::where('name', $request->office)->first();
            $office_id = $office ? $office->id : null;
        }


    
        $created = Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Service with name: ". $request->name);

        return response()->json(
                Service::selectServices(null, $request->name)
            ,200);
    }

    //update a service
    public function updateService(Request $request){
        $request->validate([
            'id' => 'required|exists:services,id',
            'name' => 'required|string',
            'description' => 'nullable|string|max:1000',
        ]);

        $existing = Service::where('name', $request->name)->get();

        count($existing) > 0 && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_SERVICE) : null ;

    
        Service::where('id', $request->id)
             ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Updated a Service with name: ". $request->name);

        return response()->json(
                Service::selectServices(null, $request->name)
            ,200);

    }

    //approve a service
    public function approveService($id){
        

        $existing = Service::selectServices($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE);
        }

    
        Service::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now(),
                'disabled_by' => null,
                'disabled_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a Service with id: ". $id);

        return response()->json(
                Service::selectServices($id, null)
            ,200);
    }

    //disable a service
    public function disableService($id){
        

        $existing = Service::selectServices($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE);
        }

    
        Service::where('id', $id)
            ->update([
                'approved_by' => null,
                'approved_at' => null,
                'disabled_by' => User::getLoggedInUserId(),
                'disabled_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_DISABLE, "Disabled a Service with id: ". $id);

        return response()->json(
                Service::selectServices($id, null)
            ,200);
    }

    //soft Delete a service
    public function softDeleteService($id){
        

        $existing = Service::selectServices($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE);
        }

    
        Service::where('id', $id)
            ->update([
                'deleted_by' => User::getLoggedInUserId(),
                'deleted_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Soft deleted a service with id: ". $id);

        return response()->json(
                Service::selectServices($id, null)
            ,200);
    }

    // restore soft-Deleted a service
    public function restoreSoftDeleteService($id){
        

        $existing = Service::where('id', $id)->whereNotNull('deleted_by')->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE);
        }

    
        Service::where('id', $id)
            ->update([
                'deleted_by' => null,
                'deleted_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored Soft deleted a service with id: ". $id);

        return response()->json(
                Service::selectServices($id, null)
            ,200);
    }

    //permanently Delete a service
    public function permanentDeleteService($id){
        

        $existing = Service::where('id', $id)->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE);
        }

    
        Service::destroy($id);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Permanently deleted a Service with name: ". $existing[0]['name']);

        return response()->json(
                []
            ,200);
    }

}
