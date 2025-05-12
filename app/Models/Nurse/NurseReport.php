<?php

namespace App\Models\Nurse;

use App\Models\Patient\Patient;
use App\Models\Patient\Visit;
use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NurseReport extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'nurse_reports';

    protected $fillable = [
        'visit_id',
        'report',
        'visit_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at'
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }

    public static function selectNurseReports($id, $visit_id){
        $nurse_reports_query = NurseReport::with([
            'visit:id,patient_id,visit_type_id,stage,open',
            'visit.patient:id,firstname,lastname,patient_code',
            'createdBy:id,email',
            'updatedBy:id,email'
        ])
        ->whereNull('nurse_reports.deleted_by');

        
        // $patients_query->with(['visits' => function ($query) {
        //     $query->orderBy('created_at', 'DESC'); // Order visits by latest first
        // }]);
        

        if($id != null){
            $nurse_reports_query->where('nurse_reports.id', $id);
        }
        elseif($visit_id != null){
            $nurse_reports_query->where('nurse_reports.visit_id', $visit_id);
        }


        $paginated_nurse_reports = $nurse_reports_query->paginate(10);
        //return $paginated_patients;
        $paginated_nurse_reports->getCollection()->transform(function ($nurse_report) {
            return NurseReport::mapResponse($nurse_report);
        });

        return $paginated_nurse_reports;

    }

    private static function mapResponse($nurse_instruction)
    {
        return [
            'id' => $nurse_instruction->id,
            'visit' => $nurse_instruction->visit,
            'report' => $nurse_instruction->report,
            'created_at' => $nurse_instruction->created_at,
            'updated_at' => $nurse_instruction->updated_at,
            'created_by' => $nurse_instruction->createdBy,
            'updated_by' => $nurse_instruction->updatedBy,
            'deleted_by' => $nurse_instruction->deletedBy,
            'deleted_at' => $nurse_instruction->deleted_at
        ];
    }

}
