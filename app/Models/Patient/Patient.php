<?php

namespace App\Models\Patient;

use App\Exceptions\InHouseUnauthorizedException;
use App\Models\Admin\ChronicDisease;
use App\Models\Admin\Employee;
use App\Models\Admin\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Utils\APIConstants;
use App\Utils\CustomUserRelations;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Patient extends Model
{
    use HasFactory;

    protected $table = "patients";

    protected $fillable = [
        'patient_code',
        'firstname',
        'lastname',
        'dob',
        'identification_type',
        'id_no',
        'scan_id_photo',
        'phonenumber1',
        'phonenumber2',
        'email',
        'address',
        'residence',
        'insurance_membership',
        'next_of_kin_name',
        'next_of_kin_contact',
        'next_of_kin_relationship',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'deleted_by',
        'deleted_at',
    ];


    use CustomUserRelations;

    //relationship with insurance Details
    public function insuranceDetails()
    {
        return $this->hasMany(InsuranceDetail::class, 'patient_id', 'id');
    }
    
    public function visits()
    {
        return $this->hasMany(Visit::class, 'patient_id', 'id');
    }

    public function chronicDiseases(){
        return $this->belongsToMany(ChronicDisease::class, 'patients_chronic_diseases_join', 'patient_id', 'chronic_disease_id');
    }

    public function PaymentMethods(){
        return $this->belongsToMany(PaymentType::class, 'patients_payment_methods_join', 'patient_id', 'payment_type_id');
    }


    //perform selection
    public static function selectPatients($id, $email, $patient_code, $id_no){
        $patients_query = Patient::with([
            'chronicDiseases:id,name',
            'PaymentMethods:id,name',
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email',
            'insuranceDetails:id,patient_id,insurer_id,scheme_type_id,member_validity', 
            'insuranceDetails.schemes:id,name',  
            'insuranceDetails.schemeTypes:id,name',  
            // 'visits:id,patient_id,stage,open',
            // 'visits.visitType:id,name',
            // 'visits.visitClinics.clinic:id,name',
            // 'visits.visitDepartments.department:id,name',
            // 'visits.visitPaymentTypes.paymentType:id,name',
            // 'visits.visitInsuranceDetails.scheme:id,name',
            // 'visits.bills.billItems.serviceItem.service:id,name',
            // 'visits.vitals:id,weight,blood_pressure,blood_glucose,height,blood_type,disease,allergies,nursing_remarks'
        ])->with(['visits' => function ($query) {
            $query->select('id', 'patient_id', 'stage', 'open')
                  ->orderBy('created_at', 'DESC')
                  ->limit(10); // Order visits by latest first
        }])
        ->whereNull('patients.deleted_by');

        
        // $patients_query->with(['visits' => function ($query) {
        //     $query->orderBy('created_at', 'DESC'); // Order visits by latest first
        // }]);
        

        if($id != null){
            $patients_query->where('patients.id', $id);
        }
        elseif($email != null){
            $patients_query->where('patients.email', $email);
        }
        elseif($patient_code != null){
            $patients_query->where('patients.patient_code', $patient_code);
        }
        elseif($id_no != null){
            $patients_query->where('patients.id_no', $id_no);
        }


        else{
            $paginated_patients = $patients_query->paginate(10);
            //return $paginated_patients;
            $paginated_patients->getCollection()->transform(function ($patient) {
                return Patient::mapResponse($patient);
            });
    
            return $paginated_patients;
        }


        return $patients_query->get()->map(function ($patient) {
            $patient_details = Patient::mapResponse($patient);

            return $patient_details;
        });


    }

    //perform selection
    public static function patientInRelationToBilledServiceSearch($request, $bill_item_status, $bill_item_payment_offer_status){

        $patients_query = Patient::with([
            'chronicDiseases:id,name',
            'PaymentMethods:id,name',
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email',
            'insuranceDetails:id,patient_id,insurer_id,scheme_type_id,member_validity', 
            'insuranceDetails.schemes:id,name',  
            'insuranceDetails.schemeTypes:id,name',  
            'visits:id,patient_id,stage,open',
            'visits.visitType:id,name',
            'visits.visitClinics.clinic:id,name',
            'visits.visitDepartments.department:id,name',
            'visits.visitPaymentTypes.paymentType:id,name',
            'visits.visitInsuranceDetails.scheme:id,name',
            'visits.bills.billItems' => function ($query) {
                $query->where('status', '!=', 'pending')
                ->orWhere('status', '!=', APIConstants::STATUS_CANCELLED); // Only load non-pending OR non cancelledbill items
            },
            'visits.bills.billItems.serviceItem.service:id,name',
            'visits.vitals:id,visit_id,systole_bp,diastole_bp,cap_refill_pressure,respiratory_rate,spo2_percentage,head_circumference_cm,height_cm,weight_kg,waist_circumference_cm,initial_medication_at_triage,bmi,food_allergy,drug_allergy,nursing_remarks'
        ])->with(['visits' => function ($query) {
            $query->select('id', 'patient_id', 'stage', 'open', 'created_at')
                  ->orderBy('id', 'DESC') // Order visits by latest first
                  ->limit(10);

                  // for the first visit, check if the visit is open
                  // $query->where('visits.open', 1);
        }])
        ->whereHas('visits.bills.billItems', function ($query) {
            $query->where('status', '!=', APIConstants::STATUS_PENDING); // Filter patients with at least one non-pending bill item
                // ->orWhere('status', '!=', APIConstants::STATUS_CANCELLED);      
        })
        ->whereNull('patients.deleted_by');

        // CHECK IF PATIENT IS EXPECTED AT TRIAGE
        Patient::checkIfPatientIsExpectedAtTriage($patients_query, $request->stage);

        // using this relationship 'visits.bills.billItems.serviceItem.service:id,name', create a query to select where serviceItem.department = 1

        if($request->patient_id != null){
            $patients_query->where('patients.id', $request->patient_id)
            ->orWhere('patients.email', 'LIKE', '%'.$request->patient_id.'%')
            ->orWhere('patients.firstname', 'LIKE', '%'.$request->patient_id.'%')
            ->orWhere('patients.lastname', 'LIKE', '%'.$request->patient_id.'%')
            ->orWhere('patients.id_no', 'LIKE', '%'.$request->patient_id.'%')
            ->orWhere('patients.phonenumber1', 'LIKE', '%'.$request->patient_id.'%')
            ->orWhere('patients.phonenumber2', 'LIKE', '%'.$request->patient_id.'%')
            ->orWhere('patients.next_of_kin_contact', 'LIKE', '%'.$request->patient_id.'%')
            ->orWhere('patients.patient_code', 'LIKE', '%'.$request->patient_id.'%');;
        }

        // get logged in user department from employees table
        $existing_employee = Employee::selectEmployees(null, null, null, Auth::user()->id);

        count($existing_employee) < 1 ? throw new InHouseUnauthorizedException("You are not granted employee status yet!") : null;

        count($existing_employee[0]['departments']) < 1 ? throw new InHouseUnauthorizedException("You are not assigned to any department yet!!!") : null;

        // // adding sort by latest created visit first        
        // $patients_query->with(['visits' => function ($query) {
        //     $query->orderBy('created_at', 'DESC')
        //     ->limit(10);; // Order visits by latest first
        // }]);

        foreach($existing_employee[0]['departments'] as $department){
            // $department->pivot->department_id
            $patients_query->whereHas('visits.bills.billItems.serviceItem', function ($query) use ($department) {
                $query->where('department_id', $department->pivot->department_id);
            });
            
        }

        // else{
            $paginated_patients = $patients_query->paginate(10);
            //return $paginated_patients;
            $paginated_patients->getCollection()->transform(function ($patient) {
                return Patient::mapResponse($patient);
            });
    
            return $paginated_patients;
        // }



        // return $patients_query->get()->map(function ($patient) {
        //     $patient_details = Patient::mapResponse($patient);

        //     return $patient_details;
        // });


    }

    //perform selection
    public static function deepSearchPatients($value){

        $patients_query = Patient::with([
            'chronicDiseases:id,name',
            'PaymentMethods:id,name',
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email',
            'insuranceDetails:id,patient_id,insurer_id,scheme_type_id,member_validity',  
            'insuranceDetails.schemes:id,name',  
            'insuranceDetails.schemeTypes:id,name',  
            'insuranceDetails.schemes:id,name',  
            'insuranceDetails.schemeTypes:id,name', 
            // 'visits:id,patient_id,stage,open',
            // 'visits.visitType:id,name',
            // 'visits.visitClinics.clinic:id,name',
            // 'visits.visitDepartments.department:id,name',
            // 'visits.visitPaymentTypes.paymentType:id,name',
            // 'visits.visitInsuranceDetails.scheme:id,name',
            // 'visits.bills.billItems.serviceItem.service:id,name',
            // 'visits.vitals:id,weight,blood_pressure,blood_glucose,height,blood_type,disease,allergies,nursing_remarks'
        ])->with(['visits' => function ($query) {
            $query->select('id', 'patient_id', 'stage', 'open')
                  ->orderBy('created_at', 'DESC'); // Order visits by latest first
        }])
        ->whereNull('patients.deleted_by')
            ->where(function ($query) use ($value) {
            $query->whereHas('insuranceDetails', function ($query) use ($value) {
                $query->where('insurance_details.principal_member_number', 'LIKE', '%' . $value . '%');
            })
            ->orWhere('patients.id', 'LIKE', '%' . $value . '%')
            ->orWhere('patients.email', 'LIKE', '%'.$value.'%')
            ->orWhere('patients.firstname', 'LIKE', '%'.$value.'%')
            ->orWhere('patients.lastname', 'LIKE', '%'.$value.'%')
            ->orWhere('patients.id_no', 'LIKE', '%'.$value.'%')
            ->orWhere('patients.phonenumber1', 'LIKE', '%'.$value.'%')
            ->orWhere('patients.phonenumber2', 'LIKE', '%'.$value.'%')
            ->orWhere('patients.next_of_kin_contact', 'LIKE', '%'.$value.'%')
            ->orWhere('patients.patient_code', 'LIKE', '%'.$value.'%');
        });

        
        // $patients_query->with(['visits' => function ($query) {
        //     $query->orderBy('created_at', 'DESC'); // Order visits by latest first
        // }])->whereHas('visits');



            // ->orWhere('patients.id', 'LIKE', '%' . $value . '%')
            // ->orWhere('patients.email', 'LIKE', '%'.$value.'%')
            // ->orWhere('patients.firstname', 'LIKE', '%'.$value.'%')
            // ->orWhere('patients.lastname', 'LIKE', '%'.$value.'%')
            // ->orWhere('patients.id_no', 'LIKE', '%'.$value.'%')
            // ->orWhere('patients.patient_code', 'LIKE', '%'.$value.'%')
            // ->orWhereHas('insuranceDetails', function ($query) use ($value) {
            //     $query->where('insurance_details.principal_member_number', 'LIKE', '%'.$value.'%'); 
            // })


        $paginated_patients = $patients_query->paginate(10);

        //return $paginated_patients;
        $paginated_patients->getCollection()->transform(function ($patient) {
            return Patient::mapResponse($patient);
        });

        return $paginated_patients;



    }

    // private function to check if patient is expected at triage (ensure that visit does not have vitals
    private static function checkIfPatientIsExpectedAtTriage($patient_query, $stage){
        if($stage == APIConstants::TRIAGE_STAGE){
            $patient_query->whereDoesntHave('visits.vitals');
        }
    }

    private static function mapResponse($patient){
        return [
            'id' => $patient->id,
            'patient_code'=>$patient->patient_code,
            'patient_firstname' => $patient->firstname,
            'patient_lastname' => $patient->lastname,
            'dob' => $patient->dob,
            'age' => $patient->dob ? now()->diffInYears($patient->dob) : null,
            'identification_type' => $patient->identification_type,
            'id_no' => $patient->id_no,
            'phonenumber1' => $patient->phonenumber1,
            'phonenumber2' => $patient->phonenumber2,
            'email' => $patient->email,
            'address' => $patient->address,
            'gender' => $patient->address,
            'occupation' => $patient->address,
            'residence' => $patient->residence, 
            'insurance_membership' => $patient->insurance_membership, 
            'next_of_kin_name' => $patient->next_of_kin_name,  
            'next_of_kin_contact' => $patient->next_of_kin_contact,  
            'next_of_kin_relationship' => $patient->next_of_kin_relationship,
            'insurance_details' => $patient->insuranceDetails,   
            'chronic_diseases' => $patient->chronicDiseases,
            'visits' => $patient->visits,
            'created_by' => $patient->createdBy ? $patient->createdBy->email : null,
            'created_at' => $patient->created_at,
            'updated_by' => $patient->updatedBy ? $patient->updatedBy->email : null,
            'updated_at' => $patient->updated_at,
            'approved_by' => $patient->approvedBy ? $patient->approvedBy->email : null,
            'approved_at' => $patient->approved_at,    

        ];
    }
}