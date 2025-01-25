<?php

namespace App\Models\Accounts;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainAccounts extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'main_accounts';

    protected $fillable = [
        'name',
        'description',
        'type',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'deleted_by',
        'deleted_at'
    ];

    public function subAccounts(){
        return $this->hasMany(SubAccounts::class, 'main_account_id', 'id');
    }


    //perform selection
    public static function selectMainAccounts($id, $name){

        // return $this->aggregateAllRels();
        $main_accounts_query = MainAccounts::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email',
            'subAccounts:id,name'
        ])->whereNull('main_accounts.deleted_by')
          ->whereNull('main_accounts.deleted_at');

        if($id != null){
            $main_accounts_query->where('main_accounts.id', $id);
        }
        elseif($name != null){
            $main_accounts_query->where('schemes.name', $name);
        }

        // return $schemes_query->get();

        return $main_accounts_query->get()->map(function ($main_account) {
            $main_account_details = [
                'id' => $main_account->id,
                'name' => $main_account->name,
                'description' => $main_account->description,
                'type' => $main_account->type,
                'sub_accounts' => $main_account->subAccounts,

            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($main_account);

            return array_merge($main_account_details, $related_user);
        });
    }

    
}
