<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitType extends Model
{
    use HasFactory;

    protected $table = 'visit_types';

    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at'
    ];

    
}
