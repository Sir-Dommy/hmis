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
use App\Models\User;
use Carbon\Carbon;

class PatientController extends Controller
{

    //saving a new patient
    public function createPatient(Request $request){
        $request->validate([
            'firstname' => 'required|string|min:2|max:100',
            'lastname'=>'required|string|min:2|max:100',
            'dob' => 'required|date|before:today',
            'phonenumber1' => 'required|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'phonenumber2' => 'string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'email' => 'required|string|email|max:255|unique:patients',
            'address' => 'required|string|min:3|max:255',
            'residence' => 'required|string|min:3|max:255'
            
        ]);

        $request-> phonenumber1 == $request-> phonenumber2 ? throw new InputsValidationException("Provided phone numbers should be different!") : null ;
            
        $patient_code = $this->generatePatientCode();

        Patient::create([
            'patient_code' => $patient_code,
            'firstname' => $request->firstname, 
            'lastname' => $request->lastname,
            'phonenumber1'=>$request->phonenumber1,
            'phonenumber2'=>$request->phonenumber2, 
            'email' => $request->email,
            'dob' => $request->dob,
            'address'=>$request->address,
            'residence'=>$request->residence,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a patient with code: ". $patient_code);

        return response()->json(
            Patient::selectPatients(null, null, $patient_code)
        ,200);

    }

   // updating a patient
    public function updatePatient(Request $request){
        $request->validate([
            'id' => 'required|integer|min:0|exists:patients,id',
            'firstname' => 'string|min:2|max:30',
            'lastname'=>'string|min:2|max:255',
            'dob' => 'date|before:today',
            'phonenumber1' => 'required|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'phonenumber2' => 'string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'email' => 'string|email|max:255',
            'address' => 'string|min:3|max:50',
            'residence' => 'string|min:3|max:50' 
        ]);

        $request-> phonenumber1 == $request-> phonenumber2 ? throw new InputsValidationException("Provided phone numbers should be different!") : null ;
         
        if(!is_null($request->email)){
            $existing = Patient::selectPatients(null, $request->email, null);

            if(count($existing) > 0 && $existing[0]["id"] != $request->id){
                throw new AlreadyExistsException(APIConstants::NAME_PATIENT. " ". $request->email);
            }
        }
        

        Patient::where('id', $request->id)
                ->update([
                    'firstname' => $request->firstname, 
                    'lastname' => $request->lastname,
                    'phonenumber1'=>$request->phonenumber1,
                    'phonenumber2'=>$request->phonenumber2,
                    'dob' => $request->dob,
                    'address'=>$request->address,
                    'email'=>$request->email,
                    'residence'=>$request->residence,
                    'updated_by' => User::getLoggedInUserId()
                ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a patient with id: ". $request->id);
        

        return response()->json(
            Patient::selectPatients($request->id, null, null)
            ,200);

    }
    //Gettind a single patients details 
    public function getSinglePatient(Request $request){

        if($request->id == null && $request->email == null && $request->patient_code == null){
            throw new InputsValidationException("id or email or patient code required!");
        }

        $patient = Patient::selectPatients($request->id, $request->email, $request->patient_code);

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

        $patients = Patient::selectPatients(null, null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all patients");


        return response()->json(
            $patients
        ,200);
    }

    //approving a patient
    public function approvePatient($id){
            
        $existing = Patient::selectPatients($id, null, null);

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
            Patient::selectPatients($id, null, null)
        ,200);
    }

    // Disabling a patient
    public function disablePatient($id){
            
        $existing = Patient::selectPatients($id, null, null);

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
            Patient::selectPatients($id, null, null)
        ,200);
    }

    public function softDelete($id){
            
        $existing = Patient::selectPatients($id, null, null);

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
            Patient::selectPatients($id, null, null)
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
