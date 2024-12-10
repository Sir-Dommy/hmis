<?php

namespace App\Models\Patient;

use App\Models\Admin\Clinic;
use App\Models\Admin\PaymentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyVisit extends Model
{
    use HasFactory;

    protected $table = 'emergency_visits';

    protected $fillable = [
        "patient_type",
        "patient_name",
        "gender",
        "age",
        "payment_type_id",
        "contact_info",
        "clinic_id",
        "doctor_id",
        "created_by",
        "updated_by",
        "deleted_by",
        "deleted_at"
    ];

    //relationship with payment Type
    public function paymentType()
    {
        return $this->hasMany(PaymentType::class, 'payment_type_id', 'id');
    }

    //relationship with payment Type
    public function clinic()
    {
        return $this->hasMany(Clinic::class, 'clinic_id', 'id');
    }

    //relationship with payment Type
    public function doctor()
    {
        return $this->hasMany(User::class, 'doctor_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }


    //perform selection
    public static function selectEmergencyVisits($id, $patient_type, $patient_name, $gender, $payment_type, $contact, $clinic, $doctor){

        // return $this->aggregateAllRels();
        $emergency_visit_query = EmergencyVisit::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'paymentType:id,payment_type_id,name',
            'clinic:id,payment_type_id,name',
            'doctor:id,doctor_id,name'
        ])->whereNull('emergency_visits.deleted_by')
          ->whereNull('emergency_visits.deleted_at');

        if($id != null){
            $emergency_visit_query->where('emergency_visits.id', $id);
        }

        $emergency_visit_query->orWhere('emergency_visits.patient_type', 'LIKE', '%'.$patient_type.'%');
        $emergency_visit_query->orWhere('emergency_visits.patient_name', 'LIKE', '%'.$patient_name.'%');
        $emergency_visit_query->orWhere('emergency_visits.gender', 'LIKE', '%'.$gender.'%');
        $emergency_visit_query->orWhere('emergency_visits.contact_info', 'LIKE', '%'.$contact.'%');
        // $emergency_visit_query->orWhere('payment_types.name', 'LIKE', '%'.$payment_type.'%');
        // $emergency_visit_query->orWhere('clinics.name', 'LIKE', '%'.$clinic.'%');
        // $emergency_visit_query->orWhere('users.email', 'LIKE', '%'.$doctor.'%');
        

        // return $schemes_query->get();
        return $emergency_visit_query->get()->map(function ($emergency_visit) {
            $emergency_visit_details = [
                'id' => $emergency_visit->id,
                'patient_name' => $emergency_visit->patient_name,
                'patient_type' => $emergency_visit->patient_type,
                'gender' => $emergency_visit->gender,
                'age' => $emergency_visit->age,
                'payment_type' => $emergency_visit->paymentType ? $emergency_visit->paymentType[0]['name'] : null,
                'contact_info' => $emergency_visit->contact_info,
                'clinic' => $emergency_visit->clinic ? $emergency_visit->clinic[0]['name'] : null,
                'doctor' => $emergency_visit->doctor ? $emergency_visit->doctor[0]['email'] : null,
                'created_by' => $emergency_visit->createdBy ? $emergency_visit->createdBy->email : null,
                'created_at' => $emergency_visit->created_at,
                'updated_by' => $emergency_visit->updatedBy ? $emergency_visit->updatedBy->email : null,
                'updated_at' => $emergency_visit->updated_at,
                
            ];

            return $emergency_visit_details;
        });
    }

}
