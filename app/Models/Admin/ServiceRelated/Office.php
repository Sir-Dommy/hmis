<?php

namespace App\Models\Admin\ServiceRelated;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'offices';

    protected $fillable = [
        'name',
        'description',
        'wing_id',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'deleted_by',
        'deleted_at'
    ];

    public function wing()
    {
        return $this->belongsTo(Wing::class, 'wing_id');
    }
}
