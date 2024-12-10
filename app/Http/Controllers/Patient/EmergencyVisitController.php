<?php

namespace App\Http\Controllers\Patient;

use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\Clinic;
use App\Models\Admin\PaymentType;
use App\Models\Patient\EmergencyVisit;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Illuminate\Http\Request;

class EmergencyVisitController extends Controller
{
    //saving a new emeregency visit
    public function createEmergencyVisit(Request $request){
        $request->validate([
            'age' => 'required|integer|min:0|max:200',
            'gender' => 'required|string|min:0|max:200',
            'payment_type'=>'string|min:1|exists:payment_type,name',
            'contact_info' => 'required|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'doctor'=>'required|string|min:1|exists:users,email',
            'clinic'=>'required|string|min:1|exists:clinics,name'
            
        ]);

        $paymentType = PaymentType::selectPaymentTypes(null, $request->payment_type);

        $doctor = User::where('email', $request->doctor)->get();

        $clinic = Clinic::selectClinics(null, $request->clinic);


        $emeregency_visit = EmergencyVisit::create([
            'patient_type' => $request->patient_type,
            'patient_name' => $request->patient_name, 
            'gender' => $request->gender,
            'age'=>$request->age,
            'payment_type_id'=>$paymentType[0]['id'], 
            'contact_info' => $request->contact_info,
            'clinic_id' => $clinic[0]['id'],
            'doctor_id'=>$doctor[0]['id'],
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Emergency visit with id: ". $emeregency_visit->id);

        return response()->json(
            EmergencyVisit::selectEmergencyVisits($emeregency_visit->id, null, null, null, null, null, null, null)
        ,200);

    }

    //update emergency visit
    public function updateEmergencyVisit(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:emergency_visits,id',
            'age' => 'required|integer|min:0|max:200',
            'gender' => 'required|string|min:0|max:200',
            'payment_type'=>'string|min:1|exists:payment_type,name',
            'contact_info' => 'required|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'doctor'=>'required|string|min:1|exists:users,email',
            'clinic'=>'required|string|min:1|exists:clinics,name'
            
        ]);

        $paymentType = PaymentType::selectPaymentTypes(null, $request->payment_type);

        $doctor = User::where('email', $request->doctor)->get();

        $clinic = Clinic::selectClinics(null, $request->clinic);


        $emeregency_visit = EmergencyVisit::where('id', $request->id)
            ->update([
                'patient_type' => $request->patient_type,
                'patient_name' => $request->patient_name, 
                'gender' => $request->gender,
                'age'=>$request->age,
                'payment_type_id'=>$paymentType[0]['id'], 
                'contact_info' => $request->contact_info,
                'clinic_id' => $clinic[0]['id'],
                'doctor_id'=>$doctor[0]['id'],
                'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Emergency visit with id: ". $emeregency_visit->id);

        return response()->json(
            EmergencyVisit::selectEmergencyVisits($emeregency_visit->id, null, null, null, null, null, null, null)
        ,200);

    }

//     //Gettind a single emergency visit 
    public function getSingleEmergencyVisit($id){

        $emergency_visit = EmergencyVisit::selectEmergencyVisits($id, null, null, null, null, null, null, null);

        if(count($emergency_visit) < 1){
            throw new NotFoundException(APIConstants::NAME_EMERGENCY_VISIT);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched an emergency visit with id: ". $emergency_visit[0]['id']);

        return response()->json(
            $emergency_visit
        ,200);
    }


    //getting all patients Details
    public function getAllEmergencyVisists(){

        $emergency_visit = EmergencyVisit::selectEmergencyVisits(null, null, null, null, null, null, null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all emergency visits");

        return response()->json(
            $emergency_visit
        ,200);
    }

    public function softDeleteEmergencyVisit($id){
            
        $existing = EmergencyVisit::selectEmergencyVisits($id, null, null, null, null, null, null, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_EMERGENCY_VISIT. " with id: ". $id);
        }
        
        EmergencyVisit::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a patient with id: ". $id);

        return response()->json(
            EmergencyVisit::selectEmergencyVisits($id, null, null, null, null, null, null, null)
        ,200);
    }

    public function permanentlyDelete($id){
            
        $existing = EmergencyVisit::selectEmergencyVisits($id, null, null, null, null, null, null, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_EMERGENCY_VISIT. " with id: ". $id);
        }
        
        EmergencyVisit::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted an emergency visit with id: ". $id);

        return response()->json(
            []
        ,200);
    }

}
