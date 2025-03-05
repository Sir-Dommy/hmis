<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceMemberShip extends Model
{
    use HasFactory;

    protected $table = 'insurance_memberships';

    protected $fillable = [
        'name',
        'active'
    ];
}
