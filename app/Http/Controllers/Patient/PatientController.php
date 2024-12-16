<?php

namespace App\Http\Controllers\Patient;

use App\Exceptions\AlreadyExistsException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient\Patient;
use Illuminate\Support\Facades\Auth;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Models\Admin\Scheme;
use App\Models\Patient\InsuranceDetail;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{

    //saving a new patient
    public function createPatient(Request $request){
        $request->validate([
            'firstname' => 'required|string|min:2|max:100',
            'lastname'=>'required|string|min:2|max:100',
            'dob' => 'required|date|before:today',
            'identification_type' => 'required|string|min:1|max:255',
            'id_no' => 'required|string|unique:patients,id_no',
            'phonenumber1' => 'required|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'phonenumber2' => 'string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'email' => 'required|string|email|max:255|unique:patients',
            'address' => 'required|string|min:3|max:255',
            'residence' => 'required|string|min:3|max:255',
            'next_of_kin_name' => 'required|string|min:3|max:255',
            'next_of_kin_contact' => 'required|string|min:3|max:255',
            'next_of_kin_relationship' => 'required|string|min:3|max:255',
            'insurer' => 'string|exists:schemes,name',
            'scheme_type' => 'string|min:3|max:255',
            'insurer_contact' => 'string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'principal_member_name' => 'string|min:3|max:255',
            'principal_member_number' => 'string|min:3|max:255',
            'member_validity' => 'string|min:3|max:255',
            'id_card_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Allowed formats and max size 2MB
            'insurance_card_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Allowed formats and max size 2MB
            
        ]);

        $request-> phonenumber1 == $request-> phonenumber2 ? throw new InputsValidationException("Provided phone numbers should be different!") : null ;
            
        $patient_code = $this->generatePatientCode();

        try{
            //handle image ya id
            $id_card_image_path = null;
            if($request->file('id_card_image')){
                $image = $request->file('id_card_image');

                // Generate a new unique name for the image
                $newName = uniqid() . '.' . $image->getClientOriginalExtension();
        
                // Store the image in the public folder
                $id_card_image_path = $image->move(public_path('images/patient/ids'), $newName);
            }

            DB::beginTransaction();
            $patient = Patient::create([
                    'patient_code' => $patient_code,
                    'firstname' => $request->firstname, 
                    'lastname' => $request->lastname,
                    'phonenumber1'=>$request->phonenumber1,
                    'phonenumber2'=>$request->phonenumber2, 
                    'email' => $request->email,
                    'dob' => $request->dob,
                    'identification_type' => $request->identification_type,
                    'id_no' => $request->id_no,
                    'scan_id_photo' => $id_card_image_path,
                    'address'=>$request->address,
                    'residence'=>$request->residence,
                    'next_of_kin_name' => $request->next_of_kin_name,
                    'next_of_kin_contact' => $request->next_of_kin_contact,
                    'next_of_kin_relationship' => $request->next_of_kin_relationship,
                    'created_by' => User::getLoggedInUserId()
                ]);

            if($request->insurer){

                $request->validate([
                    'insurer' => 'required|string|exists:schemes,name',
                    'scheme_type' => 'required|string|exists:scheme_types,name',
                    'insurer_contact' => 'required|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
                    'principal_member_name' => 'required|string|min:3|max:255',
                    'principal_member_number' => 'required|string|min:3|max:255',
                    'member_validity' => 'required|string|min:3|max:255',
                    
                ]);

                $scheme = Scheme::with([
                    'schemeTypes:id,scheme_id,name'
                ])->where('schemes.name', $request->insurer)
                    ->where('scheme_types.name', $request->scheme_type)
                    ->get();

                $scheme_type = SchemeTypes::where('name', $request->scheme_type)->get();

                count($scheme) < 1 ?? throw new InputsValidationException("Scheme type not related to provided insurer");

                //handle image ya insurance card
                $insurance_card_image_path = null;
                if($request->file('insurance_card_image')){
                    $image = $request->file('insurance_card_image');

                    // Generate a new unique name for the image
                    $newName = uniqid() . '.' . $image->getClientOriginalExtension();
            
                    // Store the image in the public folder
                    $insurance_card_image_path = $image->move(public_path('images/patient/insurance_cards'), $newName);
                }

                InsuranceDetail::create([
                    'patient_id' => $patient->id,
                    'insurer_id' => $scheme[0]['id'],
                    'scheme_type_id' => $scheme_type[0]['id'],
                    'mobile_number' => $request->insurer_contact,
                    'insurance_card_path' => $insurance_card_image_path,
                    'principal_member_name' => $request->principal_member_name,
                    'principal_member_number' => $request->principal_member_number,
                    'member_validity' => $request->member_validity,
                    'created_by' => User::getLoggedInUserId()
                ]);
            }

            DB::commit();


        }

        catch(Exception $e){
            DB::rollBack();

            throw new Exception($e);
        }
        

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a patient with code: ". $patient_code);

        return response()->json(
            Patient::selectPatients(null, null, $patient_code, null)
        ,200);

    }

   // updating a patient
    public function updatePatient(Request $request){
        $request->validate([
            'id' => 'required|exists:patients,id',
            'firstname' => 'required|string|min:2|max:100',
            'lastname'=>'required|string|min:2|max:100',
            'dob' => 'required|date|before:today',
            'identification_type' => 'required|string|min:1|max:255',
            'id_no' => 'required|string|min:3|max:255',
            'phonenumber1' => 'required|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'phonenumber2' => 'string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'email' => 'required|string|email|max:255|',
            'address' => 'required|string|min:3|max:255',
            'residence' => 'required|string|min:3|max:255',
            'next_of_kin_name' => 'required|string|min:3|max:255',
            'next_of_kin_contact' => 'required|string|min:3|max:255',
            'next_of_kin_relationship' => 'required|string|min:3|max:255',
            'insurer' => 'string|exists:schemes,name',
            'scheme_type' => 'string|min:3|max:255',
            'insurer_contact' => 'string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'principal_member_name' => 'string|min:3|max:255',
            'principal_member_number' => 'string|min:3|max:255',
            'member_validity' => 'string|min:3|max:255',
            'id_card_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Allowed formats and max size 2MB
            'insurance_card_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Allowed formats and max size 2MB
            
        ]);

        $request-> phonenumber1 == $request-> phonenumber2 ? throw new InputsValidationException("Provided phone numbers should be different!") : null ;
            
        $patient_code = $this->generatePatientCode();

        try{
            $existing = Patient::selectPatients(null, null, null, $request->id_no);

            count($existing) > 0 ?? $existing[0]['id'] != $request->id ?? throw new AlreadyExistsException(APIConstants::NAME_PATIENT);

            $existing2 = Patient::selectPatients(null, $request->email, null, null);

            count($existing2) > 0 ?? $existing[0]['id'] != $request->id ?? throw new AlreadyExistsException(APIConstants::NAME_PATIENT);

            //handle image ya id
            $id_card_image_path = null;

            if($request->file('id_card_image')){
                $image = $request->file('id_card_image');

                // Generate a new unique name for the image
                $newName = uniqid() . '.' . $image->getClientOriginalExtension();
        
                // Store the image in the public folder
                $id_card_image_path = $image->move(public_path('images/patients/ids'), $newName);
            }

            DB::beginTransaction();
            Patient::where('id', $request->id)
                ->update([
                    'patient_code' => $patient_code,
                    'firstname' => $request->firstname, 
                    'lastname' => $request->lastname,
                    'phonenumber1'=>$request->phonenumber1,
                    'phonenumber2'=>$request->phonenumber2, 
                    'email' => $request->email,
                    'dob' => $request->dob,
                    'identification_type' => $request->identification_type,
                    'id_no' => $request->id_no,
                    'scan_id_photo' => $id_card_image_path,
                    'address'=>$request->address,
                    'residence'=>$request->residence,
                    'next_of_kin_name' => $request->next_of_kin_name,
                    'next_of_kin_contact' => $request->next_of_kin_contact,
                    'next_of_kin_relationship' => $request->next_of_kin_relationship,
                    'created_by' => User::getLoggedInUserId()
                ]);

            if($request->insurer){

                $request->validate([
                    'insurer' => 'required|string|exists:schemes,name',
                    'scheme_type' => 'required|string|exists:scheme_types,name',
                    'insurer_contact' => 'required|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
                    'principal_member_name' => 'required|string|min:3|max:255',
                    'principal_member_number' => 'required|string|min:3|max:255',
                    'member_validity' => 'required|string|min:3|max:255',
                    
                ]);

                $scheme = Scheme::with([
                    'schemeTypes:id,scheme_id,name'
                ])->where('schemes.name', $request->insurer)
                    ->where('scheme_types.name', $request->scheme_type)
                    ->get();

                $scheme_type = SchemeTypes::where('name', $request->scheme_type)->get();

                count($scheme) < 1 ?? throw new InputsValidationException("Scheme type not related to provided insurer");

                //handle image ya insurance card
                $insurance_card_image_path = null;
                if($request->file('insurance_card_image')){
                    $image = $request->file('insurance_card_image');

                    // Generate a new unique name for the image
                    $newName = uniqid() . '.' . $image->getClientOriginalExtension();
            
                    // Store the image in the public folder
                    $insurance_card_image_path = $image->move(public_path('images/patient/insurance_cards'), $newName);
                }

                $insurance_details = InsuranceDetail::where('patient_id', $request->id)
                    ->where('scheme_type', $scheme_type[0]['id'])
                    ->get();

                if(count($insurance_details) < 1){
                    InsuranceDetail::create([
                            'patient_id' => $request->id,
                            'insurer_id' => $scheme[0]['id'],
                            'scheme_type_id' => $scheme_type[0]['id'],
                            'mobile_number' => $request->insurer_contact,
                            'insurance_card_path' => $insurance_card_image_path,
                            'principal_member_name' => $request->principal_member_name,
                            'principal_member_number' => $request->principal_member_number,
                            'member_validity' => $request->member_validity,
                            'updated_by' => User::getLoggedInUserId()
                        ]);
                }

                else{
                    InsuranceDetail::where('patient_id', $request->id)
                    ->where('scheme_type', $scheme_type[0]['id'])
                    ->update([
                        'patient_id' => $request->id,
                        'insurer_id' => $scheme[0]['id'],
                        'scheme_type_id' => $scheme_type[0]['id'],
                        'mobile_number' => $request->insurer_contact,
                        'insurance_card_path' => $insurance_card_image_path,
                        'principal_member_name' => $request->principal_member_name,
                        'principal_member_number' => $request->principal_member_number,
                        'member_validity' => $request->member_validity,
                        'updated_by' => User::getLoggedInUserId()
                    ]);
                }

                
            }

            DB::commit();


        }

        catch(Exception $e){
            DB::rollBack();

            throw new Exception($e);
        }
        
        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a patient with id: ". $request->id);
        

        return response()->json(
            Patient::selectPatients($request->id, null, null, null)
            ,200);

    }

    //Getting a single patients details 
    public function getSinglePatient(Request $request){

        if($request->id == null && $request->email == null && $request->patient_code == null && $request->id_no == null){
            throw new InputsValidationException("id or email or patient code or id_no required!");
        }

        $patient = Patient::selectPatients($request->id, $request->email, $request->patient_code, $request->id_no);

        if(count($patient) < 1){
            throw new NotFoundException(APIConstants::NAME_PATIENT);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a patient with id: ". $patient[0]['id']);

        return response()->json(
            $patient
        ,200);
    }

    //getting all patients Details
    public function getAllPatients(){

        $patients = Patient::selectPatients(null, null, null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all patients");


        return response()->json(
            $patients
        ,200);
    }

    //approving a patient
    public function approvePatient($id){
            
        $existing = Patient::selectPatients($id, null, null, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_PATIENT. " with id: ". $id);
        }

        Patient::where('id', $id)
                ->update([
                    'approved_by' => User::getLoggedInUserId(),  
                    'approved_at' => Carbon::now(),
                    'disabled_by' => null,
                    'disabled_at' => null,
                ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a patient with id : ". $id);

        return response()->json(
            Patient::selectPatients($id, null, null, null)
        ,200);
    }

    // Disabling a patient
    public function disablePatient($id){
            
        $existing = Patient::selectPatients($id, null, null, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_PATIENT. " with id: ". $id);
        }
        
        Patient::where('id', $id)
                ->update([
                    'approved_by' => null, 
                    'approved_at' => null,
                    'disabled_by' => User::getLoggedInUserId(),
                    'disabled_at' => Carbon::now(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_DISABLE, "Disabled a patient with id: ". $id);

        return response()->json(
            Patient::selectPatients($id, null, null, null)
        ,200);
    }

    public function softDelete($id){
            
        $existing = Patient::selectPatients($id, null, null, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_PATIENT. " with id: ". $id);
        }
        
        Patient::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a patient with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    public function permanentlyDelete($id){
            
        $existing = Patient::where('id',$id)->get();

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_PATIENT. " with id: ". $id);
        }
        
        Patient::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted a patient with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //function to generate employeecode
    private function generatePatientCode(){
        // Generate a random six-digit number
        $randomNumber = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);

        // Add the EMP prefix
        $patient_code = 'PTN' . $randomNumber;

        // Check if the code already exists in the database
        while (Patient::where('patient_code', $patient_code)->exists()) {
            $randomNumber = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
            $patient_code = 'PTN' . $randomNumber;
        }

        return $patient_code;
    }
}
