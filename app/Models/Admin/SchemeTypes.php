<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchemeTypes extends Model
{
    use HasFactory;

    protected $table = "scheme_types";

    protected $fillable = [
        "name", 
        "description",
        "scheme_id"
    ];

    

}
