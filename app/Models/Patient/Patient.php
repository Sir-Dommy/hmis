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
        'phonenumber1',
        'phonenumber2',
        'email',
        'address',
        'residence',
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


    //perform selection
    public static function selectPatients($id, $email, $patient_code){
        $patients_query = Patient::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
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

        return $patients_query->get()->map(function ($patient) {
            $patient_details = [
                'id' => $patient->id,
                'patient_code'=>$patient->patient_code,
                'patient_firstname' => $patient->firstname,
                'patient_lastname' => $patient->lastname,
                'dob' => $patient->dob,
                'phonenumber1' => $patient->phonenumber1,
                'phonenumber2' => $patient->phonenumber2,
                'email' => $patient->email,
                'address' => $patient->address,
                'residence' => $patient->residence,       

            ];

            $related_user = CustomUserRelations::relatedUsersDetails($patient);

            return array_merge($patient_details, $related_user);
        });
    }
}