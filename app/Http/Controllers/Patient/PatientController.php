<?php

namespace App\Http\Controllers\Patient;

use App\Exceptions\AlreadyExistsException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient\Patient;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Models\Admin\PaymentType;
use App\Models\Admin\Scheme;
use App\Models\Admin\SchemeTypes;
use App\Models\Patient\InsuranceDetail;
use App\Models\Patient\PatientPaymentTypesJoin;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{

    //saving a new patient
    public function createPatient(Request $request){

        $request->validate([     
            'data'=>'required|json',      
            'id_card_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Allowed formats and max size 2MB
            'insurance_card_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Allowed formats and max size 2MB
        ]);

        // NOTHING


        // Decode the JSON data
        $data = json_decode($request->input('data'), true);

        Validator::make($data, [ 
            'firstname' => 'required|string|min:2|max:100',
            'lastname'=>'required|string|min:2|max:100',
            'dob' => 'required|date|date|before_or_equal:today',
            'identification_type' => 'nullable|string|min:1|max:255',
            'id_no' => 'nullable|string|unique:patients,id_no',
            'phonenumber1' => 'nullable|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/|unique:patients,phonenumber1',
            'phonenumber2' => 'nullable|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'email' => 'nullable|string|email|max:255|unique:patients',
            'address' => 'required|string|min:3|max:255',
            'residence' => 'required|string|min:3|max:255',
            'next_of_kin_name' => 'required|string|min:3|max:255',
            'next_of_kin_contact' => 'required|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'next_of_kin_relationship' => 'required|string|min:3|max:255',
            'payment_methods' => 'required',
            'insurance_membership' => 'nullable|exists:insurance_memberships,name',
            'insurer' => 'nullable|string|exists:schemes,name',
            'scheme_type' => 'nullable|string|min:3|max:255',
            'insurer_contact' => 'nullable|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'principal_member_name' => 'nullable|string|min:3|max:255',
            'principal_member_number' => 'string|min:3|max:255',
            'member_validity' => 'nullable|date',
            
        ])->validate();

        //reassign $request variable
        $data = json_decode($request->input('data'));

        $data->phonenumber1 == $data->phonenumber2 && $data->phonenumber1 != null  ? throw new InputsValidationException("Provided phone numbers should be different!") : null ;
            
        $patient_code = $this->generatePatientCode();

        try{
            //handle image ya id
            $id_card_image_path = null;
            if($request->file('id_card_image') && $request->identification_type != null){
                $image = $request->file('id_card_image');

                // Generate a new unique name for the image
                $newName = uniqid() . '.' . $image->getClientOriginalExtension();
        
                // Store the image in the public folder
                $id_card_image_path = $image->move(public_path('images/patient/ids'), $newName);
            }

            DB::beginTransaction();
            $patient = Patient::create([
                    'patient_code' => $patient_code,
                    'firstname' => $data->firstname, 
                    'lastname' => $data->lastname,
                    'phonenumber1'=>$data->phonenumber1,
                    'phonenumber2'=>$data->phonenumber2, 
                    'email' => $data->email,
                    'dob' => $data->dob,
                    'identification_type' => $data->identification_type,
                    'id_no' => $data->id_no,
                    'scan_id_photo' => $id_card_image_path,
                    'address'=>$data->address,
                    'residence'=>$data->residence,
                    'insurance_membership' => $data->insurance_membership,
                    'next_of_kin_name' => $data->next_of_kin_name,
                    'next_of_kin_contact' => $data->next_of_kin_contact,
                    'next_of_kin_relationship' => $data->next_of_kin_relationship,
                    'created_by' => User::getLoggedInUserId()
                ]);
                
            
            $this->validateIdentification($data->identification_type, $data->id_no);
            // if insurance is selected then patient must provide their insurance details
            $this->validateInsuranceDetailsProvisionIfInsuranceMembershipIsSet($data->payment_methods, $data->insurance_details);


            if($data->insurance_details){

                foreach($data->insurance_details as $insurance_detail){

                    $validator = Validator::make((array) $insurance_detail, [
                        'insurer' => 'required|string|exists:schemes,name',
                        'scheme_type' => 'required|string|exists:scheme_types,name',
                        'insurer_contact' => 'nullable|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
                        'principal_member_name' => 'required|string|min:3|max:255',
                        'principal_member_number' => 'required|string|min:3|max:255',
                        'member_validity' => 'nullable|date',
                    ]);

                    if($validator->fails()){
                        $error_string = '';
                        foreach ($validator->errors()->all() as $error) {
                            $error_string .= $error . "\n";
                        }

                        throw new InputsValidationException($error_string);
                    }
                

                    $desiredValue = $insurance_detail->scheme_type;
                    $scheme = Scheme::with([
                        'schemeTypes:id,scheme_id,name'
                    ])->where('schemes.name', $insurance_detail->insurer)
                        ->whereHas('schemeTypes', function ($query) use ($desiredValue) {
                        $query->where('name', $desiredValue); // Condition on scheme_types table
                    })
                        ->get();
    

                    $scheme_type = SchemeTypes::where('name', $insurance_detail->scheme_type)->get();
    
                    count($scheme) < 1 ? throw new InputsValidationException("Scheme type not related to provided insurer") : null;
    
                    //handle image ya insurance card
                    $insurance_card_image_path = null;
                    if($request->file('insurance_card_image')){
                        $image = $request->file('insurance_card_image');
    
                        // Generate a new unique name for the image
                        $newName = uniqid() . '.' . $image->getClientOriginalExtension();
                
                        // Store the image in the public folder
                        $insurance_card_image_path = $image->move(public_path('images/patient/insurance_cards'), $newName);
                    }
    
                    $existing_insurance_details = InsuranceDetail::where('patient_id', $patient->id)
                                                                    ->where('insurer_id', $scheme[0]['id'])
                                                                    ->where('scheme_type_id', $scheme_type[0]['id'])
                                                                    ->get();
                    if(count($existing_insurance_details) < 1){
                        InsuranceDetail::create([
                            'patient_id' => $patient->id,
                            'insurer_id' => $scheme[0]['id'],
                            'scheme_type_id' => $scheme_type[0]['id'],
                            'mobile_number' => $insurance_detail->insurer_contact,
                            'insurance_card_path' => $insurance_card_image_path,
                            'principal_member_name' => $insurance_detail->principal_member_name,
                            'principal_member_number' => $insurance_detail->principal_member_number,
                            'member_validity' => $insurance_detail->member_validity,
                            'created_by' => User::getLoggedInUserId()
                        ]);
                    }
                    
                }

                

                
            }

            //validate and save patient payment method
            $this->validateAndSavePatientPaymentMethod($data->payment_methods, $patient->id);

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
            'data'=>'required|json',      
            'id_card_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Allowed formats and max size 2MB
            'insurance_card_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Allowed formats and max size 2MB
        ]);

        // Decode the JSON data
        $data = json_decode($request->input('data'), true);

        Validator::make($data, [ 
            'id' => 'required|exists:patients,id',
            'firstname' => 'required|string|min:2|max:100',
            'lastname'=>'required|string|min:2|max:100',
            'dob' => 'required|date|date|before_or_equal:today',
            'identification_type' => 'nullable|string|min:1|max:255',
            'id_no' => 'nullable|string|unique:patients,id_no',
            'phonenumber1' => 'nullable|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/|unique:patients,phonenumber1',
            'phonenumber2' => 'nullable|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'email' => 'nullable|string|email|max:255|unique:patients',
            'address' => 'required|string|min:3|max:255',
            'residence' => 'required|string|min:3|max:255',
            'next_of_kin_name' => 'required|string|min:3|max:255',
            'next_of_kin_contact' => 'required|string|min:3|max:255',
            'next_of_kin_relationship' => 'required|string|min:3|max:255',
            'payment_methods' => 'required',
            'insurance_membership' => 'nullable|exists:insurance_memberships,name',
            'insurer' => 'nullable|string|exists:schemes,name',
            'scheme_type' => 'nullable|string|min:3|max:255',
            'insurer_contact' => 'nullable|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
            'principal_member_name' => 'nullable|string|min:3|max:255',
            'principal_member_number' => 'string|min:3|max:255',
            'member_validity' => 'nullable|date',
            
        ])->validate();

        //reassign $request variable
        $data = json_decode($request->input('data'));

        $data->phonenumber1 == $data->phonenumber2 && $data->phonenumber1 != null  ? throw new InputsValidationException("Provided phone numbers should be different!") : null ;
            
        $patient_code = $this->generatePatientCode();

        try{
            $data->id_no != null ? $existing = Patient::selectPatients(null, null, null, $data->id_no) : $existing = [];

            count($existing) > 0 && $existing[0]['id'] != $data->id ? throw new AlreadyExistsException(APIConstants::NAME_PATIENT) : null;

            $data->email != null ? $existing2 = Patient::selectPatients(null, $data->email, null, null) : $existing2 = [];

            count($existing2) > 0 && $existing[0]['id'] != $data->id ? throw new AlreadyExistsException(APIConstants::NAME_PATIENT) : null;

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
            Patient::where('id', $data->id)
                ->update([
                    'firstname' => $data->firstname, 
                    'lastname' => $data->lastname,
                    'phonenumber1'=>$data->phonenumber1,
                    'phonenumber2'=>$data->phonenumber2, 
                    'email' => $data->email,
                    'dob' => $data->dob,
                    'identification_type' => $data->identification_type,
                    'id_no' => $data->id_no,
                    'scan_id_photo' => $id_card_image_path,
                    'address'=>$data->address,
                    'residence'=>$data->residence,
                    'insurance_membership' => $data->insurance_membership,
                    'next_of_kin_name' => $data->next_of_kin_name,
                    'next_of_kin_contact' => $data->next_of_kin_contact,
                    'next_of_kin_relationship' => $data->next_of_kin_relationship,
                    'updated_by' => User::getLoggedInUserId()
                ]);

            //validate if identification is being provided properly
            $this->validateIdentification($data->identification_type, $data->id_no);

            // if insurance is selected then patient must provide their insurance details
            $this->validateInsuranceDetailsProvisionIfInsuranceMembershipIsSet($data->payment_methods, $data->insurance_details);

            if($data->insurance_details){
                foreach($data->insurance_details as $insurance_detail){
                    $validator = Validator::make((array) $insurance_detail, [
                        'insurer' => 'required|string|exists:schemes,name',
                        'scheme_type' => 'required|string|exists:scheme_types,name',
                        'insurer_contact' => 'required|string|min:10|max:20|regex:/^\+?[0-9]{10,20}$/',
                        'principal_member_name' => 'required|string|min:3|max:255',
                        'principal_member_number' => 'required|string|min:3|max:255',
                        'member_validity' => 'nullable|date',
                        
                    ]);

                    if($validator->fails()){
                        $error_string = '';
                        foreach ($validator->errors()->all() as $error) {
                            $error_string .= $error . "\n";
                        }

                        throw new InputsValidationException($error_string);
                    }
    
                    $desiredValue = $insurance_detail->scheme_type;
                    $scheme = Scheme::with([
                        'schemeTypes:id,scheme_id,name'
                    ])->where('schemes.name', $insurance_detail->insurer)
                        ->whereHas('schemeTypes', function ($query) use ($desiredValue) {
                        $query->where('name', $desiredValue); // Condition on scheme_types table
                    })
                        ->get();
    
                    $scheme_type = SchemeTypes::where('name', $insurance_detail->scheme_type)->get();
    
                    count($scheme) < 1 ? throw new InputsValidationException("Scheme type not related to provided insurer") : null;
    
                    //handle image ya insurance card
                    $insurance_card_image_path = null;
                    if($request->file('insurance_card_image')){
                        $image = $request->file('insurance_card_image');
    
                        // Generate a new unique name for the image
                        $newName = uniqid() . '.' . $image->getClientOriginalExtension();
                
                        // Store the image in the public folder
                        $insurance_card_image_path = $image->move(public_path('images/patient/insurance_cards'), $newName);
                    }
    
                    $existing_insurance_details = InsuranceDetail::where('patient_id', $data->id)
                        ->where('insurer_id', $scheme[0]['id'])
                        ->where('scheme_type_id', $scheme_type[0]['id'])
                        ->get();
    
                    if(count($existing_insurance_details) < 1){
                        InsuranceDetail::create([
                                'patient_id' => $data->id,
                                'insurer_id' => $scheme[0]['id'],
                                'scheme_type_id' => $scheme_type[0]['id'],
                                'mobile_number' => $insurance_detail->insurer_contact,
                                'insurance_card_path' => $insurance_card_image_path,
                                'principal_member_name' => $insurance_detail->principal_member_name,
                                'principal_member_number' => $insurance_detail->principal_member_number,
                                'member_validity' => $insurance_detail->member_validity,
                                'created_by' => User::getLoggedInUserId()
                            ]);
                    }
    
                    else{
                        InsuranceDetail::where('patient_id', $data->id)
                        ->where('insurer_id', $scheme[0]['id'])
                        ->where('scheme_type_id', $scheme_type[0]['id'])
                        ->update([
                            'patient_id' => $data->id,
                            'insurer_id' => $scheme[0]['id'],
                            'scheme_type_id' => $scheme_type[0]['id'],
                            'mobile_number' => $insurance_detail->insurer_contact,
                            'insurance_card_path' => $insurance_card_image_path,
                            'principal_member_name' => $insurance_detail->principal_member_name,
                            'principal_member_number' => $insurance_detail->principal_member_number,
                            'member_validity' => $insurance_detail->member_validity,
                            'updated_by' => User::getLoggedInUserId()
                        ]);
                    }
                }

                


                
            }

            $this->validateAndSavePatientPaymentMethod($data->payment_methods, $data->id);

            DB::commit();


        }

        catch(Exception $e){
            DB::rollBack();

            throw new Exception($e);
        }
        
        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a patient with id: ". $request->id);
        

        return response()->json(
            Patient::selectPatients($data->id, null, null, null)
            ,200);

    }

    public function deepPatientSearch($value){

        $patients = Patient::deepSearchPatients($value);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Deep searched a patient with value: ". $value);

        return response()->json(
            $patients
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

    public function selectPatientsBilledForService(Request $request){
        $request->validate([
            'patient_id'=>'nullable|exists:patients,id',
            'department'=>'nullable|exists:departments,name',
            'payment_status'=>'nullable',
            'service_offer_status'=>'nullable'
        ]);

        //runc selection function in the model
        Patient::patientInRelationToBilledServiceSearch($request, null, null);


        return response()->json(
            ["sir"=>1], 200
        );
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

    private function validateAndSavePatientPaymentMethod($payment_methods, $patient_id){
        foreach($payment_methods as $payment_method){
            if($payment_method->cash == true){
                $this->saveToDB("Cash", $patient_id);
            }

            else if($payment_method->insurance == true){
                $this->saveToDB("Insurance", $patient_id);
            }


        }
    }

    private function saveToDB($payment_method, $patient_id){
        $existing_method = PaymentType::selectPaymentTypes(null, $payment_method);

        if(count($existing_method) < 1){
            DB::rollBack();
            
            throw new NotFoundException(APIConstants::NAME_PAYMENT_TYPE ." $payment_method");

        }


        $already_saved = PatientPaymentTypesJoin::where('patient_id', $patient_id)
                            ->where('payment_type_id', $existing_method[0]['id'])
                            ->get();

        if(count($already_saved)  < 1){
            PatientPaymentTypesJoin::create([
                'patient_id' => $patient_id,
                'payment_type_id' => $existing_method[0]['id']
            ]);
        }
    }

    private function validateInsuranceDetailsProvisionIfInsuranceMembershipIsSet($payment_methods, $insurer){
        foreach($payment_methods as $payment_method){
            if($payment_method->insurance == true){
                count($insurer) == 0 ? throw new InputsValidationException("INSURANCE DETAILS MUST BE PROVIDED INSURANCE AS YOUR PAYMENT METHOD!!!!") : null;
         
            }


        }
    }

    private function validateIdentification($identification_type, $id_no){
        ($identification_type == null && $id_no != null) || ($identification_type != null && $id_no == null) ? throw new InputsValidationException("You must provide id number with its related identification type (Both must be provided or omitted simultaneously") : null;
    }
}
