<?php 

namespace App\Utils;

use App\Models\User;

trait CustomUserRelations {
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public static function relatedUsersDetails($data){
        return [
            'created_by' => $data->createdBy ? $data->createdBy->email : null,
            'created_at' => $data->created_at,
            'updated_by' => $data->updatedBy ? $data->updatedBy->email : null,
            'updated_at' => $data->updated_at,
            'approved_by' => $data->approvedBy ? $data->approvedBy->email : null,
            'approved_at' => $data->approved_at,
        ];

    }
}