<?php

namespace App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Utils\CustomUserRelations;

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


    //perform selection
    public static function selectPatients($id, $email, $patient_code, $id_no){
        $patients_query = Patient::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email',
            'insuranceDetails:id,patient_id,member_validity'
        ])->whereNull('patients.deleted_by');

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

    private static function mapResponse($patient){
        return [
            'id' => $patient->id,
            'patient_code'=>$patient->patient_code,
            'patient_firstname' => $patient->firstname,
            'patient_lastname' => $patient->lastname,
            'dob' => $patient->dob,
            'identification_type' => $patient->identification_type,
            'id_no' => $patient->id_no,
            'phonenumber1' => $patient->phonenumber1,
            'phonenumber2' => $patient->phonenumber2,
            'email' => $patient->email,
            'address' => $patient->address,
            'residence' => $patient->residence,  
            'next_of_kin_name' => $patient->next_of_kin_name,  
            'next_of_kin_contact' => $patient->next_of_kin_contact,  
            'next_of_kin_relationship' => $patient->next_of_kin_relationship,
            'insurance_details' => $patient->insuranceDetails,   
            'created_by' => $patient->createdBy ? $patient->createdBy->email : null,
            'created_at' => $patient->created_at,
            'updated_by' => $patient->updatedBy ? $patient->updatedBy->email : null,
            'updated_at' => $patient->updated_at,
            'approved_by' => $patient->approvedBy ? $patient->approvedBy->email : null,
            'approved_at' => $patient->approved_at,    

        ];
    }
}