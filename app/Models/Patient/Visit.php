<?php

namespace App\Models\Patient;

use App\Models\Admin\Clinic;
use App\Models\Admin\Department;
use App\Models\Admin\PaymentType;
use App\Models\Admin\Scheme;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $table = "visits";

    protected $fillable = [
        "patient_id",
        "claim_number",
        "amount",
        "department_id",
        "clinic_id",
        "visit_type",
        "scheme_id",
        "fee_type",
        "stage",
        "open",
        "document_path",
        "created_by",
        "updated_by",
        "deleted_by",
        "deleted_at"
    ];

    //relationship with clinic
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    //relationship with patient
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    //relationship with department
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }


    //relationship with department
    public function feeType()
    {
        return $this->belongsTo(PaymentType::class, 'fee_type');
    }

    //relationship with payment Type
    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
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
            'clinic:id,name',
            'department:id,name',
            'feeType:id,name',
            'scheme:id,name',
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
            'claim_number' => $visit->claim_number,
            'amount' => $visit->amount,
            'department' => $visit->department->name,
            'clinic' => $visit->clinic ? $visit->clinic->name : null,
            'visit_type' => $visit->visit_type,
            'scheme' => $visit->scheme->name,
            'fee_type' => $visit->feeType ? $visit->feeType->name : null,
            'stage' => $visit->stage,
            'open' => $visit->open,
            'vitals' => $visit->vitals,
            'created_by' => $visit->createdBy ? $visit->createdBy->email : null,
            'created_at' => $visit->created_at,
            'updated_by' => $visit->updatedBy ? $visit->updatedBy->email : null,
            'updated_at' => $visit->updated_at,
        ];
    }

}
