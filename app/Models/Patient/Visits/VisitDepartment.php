<?php

namespace App\Models\Patient\Visits;

use App\Models\Admin\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitDepartment extends Model
{
    use HasFactory;

    protected $table = 'visit_patient_departments';

    protected $fillable = [
        'visit_id',
        'department_id',
        'time_in',
        'time_out'
    ];

    //relationship with department
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
