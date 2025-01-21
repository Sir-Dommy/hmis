<?php

namespace App\Models\Admin\ServiceRelated;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationCategory extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'consultation_categories';

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'deleted_by',
        'deleted_at'
    ];
}
