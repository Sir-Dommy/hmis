<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vital extends Model
{

    use HasFactory;

    protected $table = "vitals";

    protected $fillable = [
        'visits_id',
        'weight', 
        'blood_pressure',
        'blood_glucose',
        'height', 
        'blood_type',
        'disease',
        'allergies',
        'nursing_remarks',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at'
    ];



        public function vitals()
    {
        return $this->hasMany(Vital::class, 'visits_id');
    }

    //perform selection
    public static function selectVitals($id, $visits_id){
        $vitals_query = Vital::with([
            'createdBy:id',
            'updatedBy:id',
        ])->whereNull('vitals.deleted_by');

        if($id != null){
            $vitals_query->where('vitals.id', $id);
        }
        elseif($visits_id != null){
            $vitals_query->where('patients.visits_id', $visits_id);
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
            'visits_id'=>$vital->visits_id,
            'weight'=>$vital->weight, 
            'blood_pressure'=>$vital->blood_pressure,
            'blood_glucose'=>$vital->blood_glucose,
            'height'=>$vital->height, 
            'blood_type'=>$vital->blood_type,
            'disease'=>$vital->disease,
            'allergies'=>$vital->allergies,
            'nursing_remarks'=>$vital->nursing_remarks,
            'created_by'=>$vital->created_by,
            'updated_by'=>$vital->updated_by,
            'deleted_by'=>$vital->deleted_by,
            'deleted_at'=>$vital->deleted_at, 

        ];
    }
}
