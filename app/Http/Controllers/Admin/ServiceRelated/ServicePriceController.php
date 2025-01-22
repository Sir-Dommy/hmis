<?php

namespace App\Http\Controllers\Admin\ServiceRelated;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
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
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicePriceController extends Controller
{
    //get all service prices
    public function getAllServicePrices(){        

        $all = ServicePrice::selectServicePrice(null, null, null, null, null, null, null, null,
        null, null, null, null, null, null, null, null, null, null, null, null,
        null, null, null
        );

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Service Prices");

        return response()->json(
                $all ,200);
    }

    //get a single service price
    public function getSingleServicePrice($id){   
        
        $all = ServicePrice::selectServicePrice($id, null, null, null, null, null, null, null,
        null, null, null, null, null, null,  null, null, null, null, null, null,
        null, null, null
        );

        count($all) < 1 ? throw new NotFoundException(APIConstants::NAME_SERVICE_PRICE) : null;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched Service Price with id: ".$all[0]['id']);

        return response()->json(
                $all ,200);
    }
    
    //create a service price
    public function createServicePrice(Request $request){
        $request->validate([
            'service' => 'required|exists:services,name',
            'price' => 'required|numeric',
        ]);
        

        // Initialize variables for each entity
        $service_id = null;

        // Perform individual queries and assign the ids or null
        if ($request->has('service')) {
            $service = Service::where('name', $request->service)->first();
            $service_id = $service ? $service->id : null;
        }
    
        DB::beginTransaction();

        try{
            $created = ServicePrice::create([
                'service_id' => $service_id,
                'price' => $request->price,
                'price_applies_from' => $request->price_applies_from,
                'price_applies_to' => $request->price_applies_to,
                'duration' => $request->duration,
                'created_by' => User::getLoggedInUserId()
            ]);

            //save other details of service price
            $this->saveServicePrice($request, $created->id);

            DB::commit();

        }

        catch(Exception $e){
            DB::rollBack();
            throw new Exception($e);

        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Service price with id: ". $created->id);

        return response()->json(
                ServicePrice::selectServicePrice($created->id, null, null, null, null, null, null, null,
                null, null, null, null,  null, null, null, null, null, null, null, null,
                null, null, null)
            ,200);
    }

    //update a service price
    public function updateServicePrice(Request $request){
        $request->validate([
            'id' => 'required|exists:service_prices,id',
        ]);

        //save update details
        $this->saveServicePrice($request, $request->id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Service price with name: ". $request->id);

        return response()->json(
            ServicePrice::selectServicePrice($request->id, null, null, null, null, null, null, null,
                null, null, null, null, null, null, null, null, null, null, null, null,
                null, null, null)
            ,200);

    }

    //approve a service price
    public function approveServicePrice($id){
        

        $existing = ServicePrice::selectServicePrice($id, null, null, null, null, null, null, null,
            null, null, null, null, null, null,  null, null, null, null, null, null,
            null, null, null
        );
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE_PRICE);
        }

    
        ServicePrice::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now(),
                'disabled_by' => null,
                'disabled_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a Service price with id: ". $id);

        return response()->json(
            ServicePrice::selectServicePrice($id, null, null, null, null, null, null, null,
                null, null, null, null, null, null, null, null, null, null, null, null,
                null, null, null
            )
            ,200);
    }

    //disable a service price
    public function disableServicePrice($id){
        

        $existing = ServicePrice::selectServicePrice($id, null, null, null, null, null, null, null,
            null, null, null, null,  null, null, null, null, null, null, null, null,
            null, null, null
        );
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE_PRICE);
        }

    
        ServicePrice::where('id', $id)
            ->update([
                'approved_by' => null,
                'approved_at' => null,
                'disabled_by' => User::getLoggedInUserId(),
                'disabled_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_DISABLE, "Disabled a Service price with id: ". $id);

        return response()->json(
            ServicePrice::selectServicePrice($id, null, null, null, null, null, null, null,
                null, null, null, null, null, null, null, null, null, null, null, null,
                null, null, null
            )
            ,200);
    }

    //soft Delete a service price
    public function softDeleteServicePrice($id){
        

        $existing = ServicePrice::selectServicePrice($id, null, null, null, null, null, null, null,
            null, null, null, null, null, null, null, null, null, null, null, null,
            null, null, null
        );
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE_PRICE);
        }

    
        ServicePrice::where('id', $id)
            ->update([
                'deleted_by' => User::getLoggedInUserId(),
                'deleted_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Soft deleted a service price with id: ". $id);

        return response()->json(
            ServicePrice::selectServicePrice($id, null, null, null, null, null, null, null,
                null, null, null, null, null, null, null, null, null, null, null, null,
                null, null, null
            )
            ,200);
    }

    // restore soft-Deleted a service price
    public function restoreSoftDeleteServicePrice($id){
        

        $existing = ServicePrice::where('id', $id)
                ->whereNotNull('deleted_by')
                ->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE_PRICE);
        }

    
        ServicePrice::where('id', $id)
            ->update([
                'deleted_by' => null,
                'deleted_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored Soft deleted a service price with id: ". $id);

        return response()->json(
            ServicePrice::selectServicePrice($id, null, null, null, null, null, null, null,
                null, null, null, null, null, null, null, null, null, null, null, null,
                null, null, null
            ) 
            ,200);
    }

    //permanently Delete a service price
    public function permanentDeleteServicePrice($id){
        

        $existing = ServicePrice::selectServicePrice($id, null, null, null, null, null, null, null,
            null, null, null, null, null,  null, null, null, null, null, null, null,
            null, null, null
        );
            
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE_PRICE);
        }

    
        ServicePrice::destroy($id);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Permanently deleted a Service price with id: ". $existing[0]['id']);

        return response()->json(
                []
            ,200);
    }

    //validate all details and save details
    private function saveServicePrice($request, $id){
        $request->validate([
            'service' => 'required|exists:services,name',
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
            'price' => 'required|numeric', // Price should be numeric
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


        //extra validation

        // check if service price exists
        $existing_price = ServicePrice::selectServicePrice(null, $request->service, $request->department, $request->consultation_category, $request->clinic, $request->payment_type, $request->scheme, $request->scheme_type,
            $request->consultation_type, $request->visit_type, $request->doctor, $request->price_applies_from, $request->price_applies_to, $request->duration, $request->lab_test_type, $request->image_test_type, $request->drug_id, $request->brand, $request->branch, $request->building,
            $request->wing, $request->ward, $request->office
         );

        //  $existing_price = json_decode($existing_price); // Decode the JSON response into an associative array

        foreach($existing_price as $exists){

            $exists['id'] != $id  ? throw new AlreadyExistsException(APIConstants::NAME_SERVICE_PRICE) : null;

            
        }

         


        // Find the record
        $servicePrice = ServicePrice::find($id);

        if (!$servicePrice) {
            throw new NotFoundException(APIConstants::NAME_SERVICE_PRICE);
        }


         //$existing->total() < 1 ?? throw new NotFoundException(APIConstants::NAME_SERVICE_PRICE) ;

        // Update each column independently
        $servicePrice->service_id = $service_id;
        $servicePrice->department_id = $department_id;
        $servicePrice->consultation_category_id = $consultation_category_id;
        $servicePrice->clinic_id = $clinic_id;
        $servicePrice->payment_type_id = $payment_type_id;
        $servicePrice->scheme_id = $scheme_id;
        $servicePrice->scheme_type_id = $scheme_type_id;
        $servicePrice->consultation_type_id = $consultation_type_id;
        $servicePrice->visit_type_id = $visit_type_id;
        $servicePrice->doctor_id = $doctor_id;
        $servicePrice->price_applies_from = $request->price_applies_from;
        $servicePrice->price_applies_to = $request->price_applies_to;
        $servicePrice->duration = $request->duration;
        $servicePrice->lab_test_type_id = $lab_test_type_id;
        $servicePrice->image_test_type_id = $image_test_type_id;
        $servicePrice->drug_id = $drug_id;
        $servicePrice->brand_id = $brand_id;
        $servicePrice->branch_id = $branch_id;
        $servicePrice->building_id = $building_id;
        $servicePrice->wing_id = $wing_id;
        $servicePrice->ward_id = $ward_id;
        $servicePrice->office_id = $office_id;
        $servicePrice->price = $request->price;

        // Save the updated model
        $servicePrice->save();
    }

}
