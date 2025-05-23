<?php

namespace App\Models\Patient;

use App\Models\Admin\Diagnosis;
use App\Models\Admin\VisitType;
use App\Models\Bill\Bill;
use App\Models\Doctor\Consultation;
use App\Models\Laboratory\OrderedTests;
use App\Models\Nurse\NurseInstruction;
use App\Models\Patient\Visits\AppointmentFollowUps;
use App\Models\Patient\Visits\VisitClinic;
use App\Models\Patient\Visits\VisitDepartment;
use App\Models\Patient\Visits\VisitInsuranceDetail;
use App\Models\Patient\Visits\VisitPaymentType;
use App\Models\Pharmacy\Prescription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $table = "visits";

    protected $fillable = [
        "patient_id",
        "visit_type_id",
        "stage",
        "open",
        'bar_code',
        "created_by",
        "updated_by",
        "deleted_by",
        "deleted_at"
    ];

    

    //relationship with patient
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }


    public function visitClinics()
    {
        return $this->hasMany(VisitClinic::class, 'visit_id', 'id');
    }

    public function visitDepartments()
    {
        return $this->hasMany(VisitDepartment::class, 'visit_id', 'id');
    }


    public function visitInsuranceDetails()
    {
        return $this->hasMany(VisitInsuranceDetail::class, 'visit_id', 'id');
    }


    public function visitPaymentTypes()
    {
        return $this->hasMany(VisitPaymentType::class, 'visit_id', 'id');
    }


    public function bills()
    {
        return $this->hasMany(Bill::class, 'visit_id', 'id');
    }

    //relationship with visit types
    public function visitType()
    {
        return $this->belongsTo(VisitType::class, 'visit_type_id');
    }


    public function consultation()
    {
        return $this->hasMany(Consultation::class, 'visit_id', 'id');
    }


    public function orderTests()
    {
        return $this->hasMany(OrderedTests::class, 'visit_id', 'id');
    }


    public function prescription()
    {
        return $this->hasMany(Prescription::class, 'visit_id', 'id');
    }


    public function nurseOrders()
    {
        return $this->hasMany(NurseInstruction::class, 'visit_id', 'id');
    }


    public function followUps()
    {
        return $this->hasMany(AppointmentFollowUps::class, 'visit_id', 'id');
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

    public function vitals()
    {
        return $this->hasMany(Vital::class, 'visit_id', 'id');
    }


    //perform selection
    public static function selectVisits($id){

        // return $this->aggregateAllRels();
        $visit_query = Visit::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'patient:id,patient_code',
            'visitType:id,name',
            'visitClinics.clinic:id,name',
            'visitDepartments.department:id,name',
            'visitPaymentTypes:id,visit_id,payment_type_id',
            'visitPaymentTypes.paymentType:id,name',
            'visitInsuranceDetails:id,visit_id,scheme_id,scheme_type_id,claim_number,available_balance,signature',
            'visitInsuranceDetails.scheme:id,name',
            'visitInsuranceDetails.scheme.schemeTypes:id,name,max_visits_per_year,max_amount_per_visit',
            'bills:id,visit_id,bill_reference_number',
            'bills.billItems:id,bill_id,status,offer_status,service_item_id',
            'bills.billItems.serviceItem:id,service_id',
            'bills.billItems.serviceItem.service:id,name',
            'vitals:id,visit_id,systole_bp,diastole_bp,cap_refill_pressure,respiratory_rate,spo2_percentage,head_circumference_cm,height_cm,weight_kg,waist_circumference_cm,initial_medication_at_triage,bmi,food_allergy,drug_allergy,nursing_remarks'
        ])->whereNull('visits.deleted_by')
          ->whereNull('visits.deleted_at');

        if($id != null){
            $visit_query->where('visits.id', $id);

            // return $schemes_query->get();
            return $visit_query->get()->map(function ($visit) {
                $visit_details = Visit::mapResponse($visit);

                return $visit_details;
            });
        }


        $paginated_visits = $visit_query->paginate(10);
        
        $paginated_visits->getCollection()->transform(function ($visit) {
            return Visit::mapResponse($visit);
        });

        return $paginated_visits;
        // $visit_query = $visit_query->paginate(10);
        
    }

    private static function mapResponse($visit){
        return [
            'id' => $visit->id,
            'patient_id' => $visit->patient_id,
            'patient_code' => $visit->patient ? $visit->patient->patient_code : null,
            'departments' => $visit->VisitDepartments,
            'clinics' => $visit->visitClinics,
            'visit_type' => $visit->visitType ? $visit->visitType->name : null,
            'schemes' => $visit->visitInsuranceDetails,
            'payment_types' => $visit->visitPaymentTypes,
            'bill' => $visit->bills,
            'stage' => $visit->stage,
            'open' => $visit->open,
            'bar_code'=>$visit->bar_code,
            'vitals' => $visit->vitals,
            'created_by' => $visit->createdBy ? $visit->createdBy->email : null,
            'created_at' => $visit->created_at,
            'updated_by' => $visit->updatedBy ? $visit->updatedBy->email : null,
            'updated_at' => $visit->updated_at,
        ];
    }

}
