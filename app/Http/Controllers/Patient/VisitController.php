<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Admin\Clinic;
use App\Models\Admin\Department;
use App\Models\Admin\PaymentType;
use App\Models\Admin\Scheme;
use App\Models\Patient\Visit;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    //saving a new emergency visit
    public function createVisit(Request $request){
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'claim_number' => 'required|string|min:1|max:255',
            'amount'=>'required|numeric|min:0|',
            'department' => 'required|string|exists:departments,name',
            'clinic'=>'required|string|exists:clinics,name',
            'visit_type'=>'required|string|min:1|max:255',
            'scheme' => 'required|string|exists:schemes,name',
            'fee_type'=>'required|string|exists:payment_types,name',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Allowed formats and max size 2MB
        
        ]);

        //store image first 
        // Get the uploaded file
        if($request->file('image')){
            $image = $request->file('image');

            // Generate a new unique name for the image
            $newName = uniqid() . '.' . $image->getClientOriginalExtension();
    
            // Store the image in the public folder
            $path = $image->move(public_path('images/claims'), $newName);
        }
        

        $department = Department::where('name', $request->department)->get("id");
        $clinic = Clinic::where('name', $request->clinic)->get("id");
        $scheme = Scheme::where('name', $request->scheme)->get("id");
        $fee_type = PaymentType::where('name', $request->fee_type)->get("id");

        return response()->file($path);
        echo $department[0]['id'];
        // $visit = Visit::create([
        //     'patient_id' => $request->patient_id,
        //     'claim_number' => $request->claim_number, 
        //     'amount' => $request->amount,
        //     'department_id'=>$department,
        //     'clinic_id'=>$clinic, 
        //     'visit_type' => $request->visit_type,
        //     'scheme_id' => $scheme,
        //     'fee_type'=>$fee_type,
        //     'stage'=>0,
        //     'document_path'=>$path,
        //     'created_by' => User::getLoggedInUserId()
        // ]);

        // UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a visit with id: ". $visit->id);

        // return response()->json(
        //     Visit::selectVisits($visit->id)
        // ,200);

    }

//     //update emergency visit
//     public function updateEmergencyVisit(Request $request){
//         $request->validate([
//             'id' => 'required|exists:emergency_visits,id',
//             'age' => 'required|integer|min:0|max:200',
//             'gender' => 'required|string|min:0|max:200',
//             'payment_type'=>'string|min:1|exists:payment_types,name',
//             'contact_info' => 'required|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
//             'doctor'=>'required|string|min:1|exists:users,email',
//             'clinic'=>'required|string|min:1|exists:clinics,name'
            
//         ]);


//         $paymentType = PaymentType::selectPaymentTypes(null, $request->payment_type);

//         $doctor = User::where('email', $request->doctor)->get();

//         $clinic = Clinic::selectClinics(null, $request->clinic);


//         EmergencyVisit::where('id', $request->id)
//             ->update([
//                 'patient_type' => $request->patient_type,
//                 'patient_name' => $request->patient_name, 
//                 'gender' => $request->gender,
//                 'age'=>$request->age,
//                 'payment_type_id'=>$paymentType[0]['id'], 
//                 'contact_info' => $request->contact_info,
//                 'clinic_id' => $clinic[0]['id'],
//                 'doctor_id'=>$doctor[0]['id'],
//                 'created_by' => User::getLoggedInUserId()
//         ]);

//         UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Emergency visit with id: ". $request->id);

//         return response()->json(
//             EmergencyVisit::selectEmergencyVisits($request->id, null, null, null, null, null, null, null)
//         ,200);

//     }

// //     //Gettind a single emergency visit 
//     public function getSingleEmergencyVisit($id){

//         $emergency_visit = EmergencyVisit::selectEmergencyVisits($id, null, null, null, null, null, null, null);

//         if(count($emergency_visit) < 1){
//             throw new NotFoundException(APIConstants::NAME_EMERGENCY_VISIT);
//         }

//         UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched an emergency visit with id: ". $emergency_visit[0]['id']);

//         return response()->json(
//             $emergency_visit
//         ,200);
//     }


    //getting all patients Details
    public function getAllVisits(){

        $visits = Visit::selectVisits(null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all visits");

        return response()->json(
            $visits
        ,200);
    }

    // public function softDeleteEmergencyVisit($id){
            
    //     $existing = EmergencyVisit::selectEmergencyVisits($id, null, null, null, null, null, null, null);

    //     if(count($existing) < 1){
    //         throw new NotFoundException(APIConstants::NAME_EMERGENCY_VISIT. " with id: ". $id);
    //     }
        
    //     EmergencyVisit::where('id', $id)
    //             ->update([
    //                 'deleted_at' => now(),
    //                 'deleted_by' => User::getLoggedInUserId(),
    //             ]);


    //     UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a patient with id: ". $id);

    //     return response()->json(
    //         EmergencyVisit::selectEmergencyVisits($id, null, null, null, null, null, null, null)
    //     ,200);
    // }

    // public function permanentlyDelete($id){
            
    //     $existing = EmergencyVisit::where("id",$id)->get();

    //     if(count($existing) < 1){
    //         throw new NotFoundException(APIConstants::NAME_EMERGENCY_VISIT. " with id: ". $id);
    //     }
        
    //     EmergencyVisit::destroy($id);


    //     UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted an emergency visit with id: ". $id);

    //     return response()->json(
    //         []
    //     ,200);
    // }

}