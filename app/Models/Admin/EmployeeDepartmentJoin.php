<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDepartmentJoin extends Model
{
    use HasFactory;

    protected $table = 'employee_department_join';

    protected $fillable = [
        'employee_id',
        'department_id'
    ];
}
