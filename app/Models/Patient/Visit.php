<?php

namespace App\Models\Patient;

use App\Models\Admin\VisitType;
use App\Models\Bill\Bill;
use App\Models\Patient\Visits\VisitClinic;
use App\Models\Patient\Visits\VisitDepartment;
use App\Models\Patient\Visits\VisitInsuranceDetail;
use App\Models\Patient\Visits\VisitPaymentType;
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

    //relationship with department
    public function visitType()
    {
        return $this->belongsTo(VisitType::class, 'visit_type_id');
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
            'visitPaymentTypes.paymentType:id,name',
            'visitInsuranceDetails:id,visit_id,scheme_id,claim_number,available_balance,signature',
            'visitInsuranceDetails.scheme:id,name',
            'bills:id,bill_reference_number',
            'bills.billItems:id,status,offer_status',
            'bills.billItems.serviceItem.service:id,name',
            'vitals:id,visit_id,weight,blood_pressure,blood_glucose,height,blood_type,disease,allergies,nursing_remarks'
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
