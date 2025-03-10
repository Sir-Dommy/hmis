<?php

namespace App\Models\Patient\Visits;

use App\Models\Patient\Patient;
use App\Models\User;
use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentFollowUps extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = "appointments_follow_ups";

    protected $fillable = [
        "patient_id",
        "visit_id",
        "who_to_see",
        "appointment_date_time",
        "appointment_type",
        "description",
        "created_by",
        "updated_by",
        "approved_by",
        "approved_at",
        "deleted_by",
        "deleted_at",
    ];

    //relationship with patient
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    
    //relationship with users ....who to see
    public function whoToSee()
    {
        return $this->belongsTo(User::class, 'who_to_see');
    }

    //perform selection
    public static function selectAppointmentsAndFollowUps($id, $name){

        $appointments_follow_ups_query = AppointmentFollowUps::with([
            'patient:id,patient_code',
            'whoToSee:id,email',
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('main_services.deleted_by')
          ->whereNull('main_services.deleted_at');

        if($id != null){
            $appointments_follow_ups_query->where('appointments_follow_ups.id', $id);
        }


        return $appointments_follow_ups_query->get()->map(function ($appointments_follow_ups) {
            $appointments_follow_ups_details = [
                'id' => $appointments_follow_ups->id,
                'name' => $appointments_follow_ups->name,
                'description' => $appointments_follow_ups->description,
                'patient_code' => $appointments_follow_ups->patient_code,
                'who_to_visit' => $appointments_follow_ups->whoToSee->email,
                'visit_id' => $appointments_follow_ups->visit_id,
                
            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($appointments_follow_ups);

            return array_merge($appointments_follow_ups_details, $related_user);
        });

    }
}
