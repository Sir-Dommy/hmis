<?php

namespace App\Models\Admin\ServiceRelated;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wing extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'wings';

    protected $fillable = [
        'name',
        'description',
        'building_id',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'deleted_by',
        'deleted_at'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }
}
