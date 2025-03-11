<?php

namespace App\Models\Pharmacy;

use App\Models\Admin\Drug;
use App\Models\Patient\Visit;
use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'prescriptions';

    protected $fillable = [
        'visit_id',
        'drug',
        'drug_formula',
        'brand',
        'dosage_instruction',
        'prescription_instruction',
        'status',
        'end_time',
        "created_by",
        "updated_by",
        "deleted_by",
        "deleted_at"
    ];

    public function visit(){
        return $this->belongsTo(Visit::class, 'visit_id');
    }

     //perform selection
     public static function selectPrescriptions($id){

        $prescriptions_query = Prescription::with([
            'visit:id,patient_id',
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('prescriptions.deleted_by')
          ->whereNull('prescriptions.deleted_at');

        if($id != null){
            $prescriptions_query->where('prescriptions.id', $id);
        }



        return $prescriptions_query->get()->map(function ($prescription) {
            $prescriptions_details = [
                'id' => $prescription->id,
                'visit_id' => $prescription->visit_id,
                'patient_id' => $prescription->visit->patient_id,
                'drug_formula' => $prescription->drug_formula,
                'brand' => $prescription->brand,
                'dosage_instruction' => $prescription->dosage_instruction,
                'prescription_instruction' => $prescription->prescription_instruction,
                'status' => $prescription->status,
                'end_time' => $prescription->end_time,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($prescription);

            return array_merge($prescriptions_details, $related_user);
        });

    }
}
