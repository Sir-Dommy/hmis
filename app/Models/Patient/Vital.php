<?php

namespace App\Models\Patient;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vital extends Model
{

    use HasFactory;

    use CustomUserRelations;

    protected $table = "vitals";

    protected $fillable = [
        'visit_id',
        'systole_bp',
        'diastole_bp',
        'cap_refill_pressure',
        'respiratory_rate',
        'spo2_percentage',
        'head_circumference_cm',
        'height_cm',
        'weight_kg',
        'blood_glucose',
        'temperature',
        'waist_circumference_cm',
        'initial_medication_at_triage',
        'bmi',
        'food_allergy',
        'drug_allergy',
        'nursing_remarks',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at'
    ];


    //perform selection
    public static function selectVitals($id, $visit_id){
        $vitals_query = Vital::with([
            'createdBy:id',
            'updatedBy:id',
        ])->whereNull('vitals.deleted_by');

        if($id != null){
            $vitals_query->where('vitals.id', $id);
        }
        elseif($visit_id != null){
            $vitals_query->where('vitals.visit_id', $visit_id);
        }
       
        else{
            $paginated_vitals = $vitals_query->paginate(10);
        
            $paginated_vitals->getCollection()->transform(function ($vital) {
                return Vital::mapResponse($vital);
            });
    
            return $paginated_vitals;
        }



        return $vitals_query->get()->map(function ($vital) {
            $vital_details = Vital::mapResponse($vital);

            return $vital_details;
        });


    }

    private static function mapResponse($vital){
        return [
            'id' => $vital->id,
            'visit_id'=>$vital->visit_id,
            'systole_bp'=>$vital->systole_bp,
            'diastole_bp'=>$vital->diastole_bp,
            'cap_refill_pressure'=>$vital->cap_refill_pressure,
            'respiratory_rate'=>$vital->respiratory_rate,
            'spo2_percentage'=>$vital->spo2_percentage,
            'head_circumference_cm'=>$vital->head_circumference_cm,
            'height_cm'=>$vital->height_cm,
            'weight_kg'=>$vital->weight_kg,
            'blood_glucose'=>round($vital->blood_glucose, 2),
            'temperature'=>round($vital->temperature, 2),
            'waist_circumference_cm'=>$vital->waist_circumference_cm,
            'initial_medication_at_triage'=>$vital->initial_medication_at_triage,
            'bmi'=>$vital->bmi,
            'food_allergy'=>$vital->food_allergy,
            'drug_allergy'=>$vital->drug_allergy,
            'nursing_remarks'=>$vital->nursing_remarks,
            'created_by'=>$vital->created_by,
            'updated_by'=>$vital->updated_by,
            'deleted_by'=>$vital->deleted_by,
            'deleted_at'=>$vital->deleted_at, 

        ];
    }
}
