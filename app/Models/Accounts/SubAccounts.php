<?php

namespace App\Models\Accounts;

use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAccounts extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'sub_accounts';

    protected $fillable = [
        'name',
        'main_account_id',
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

    public function mainAccount(){
        return $this->belongsTo(MainAccounts::class, 'main_account_id');
    }


    //perform selection
    public static function selectSubAccounts($id, $name){

        // return $this->aggregateAllRels();
        $sub_accounts_query = SubAccounts::with([
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email',
            'subAccounts:id,name'
        ])->whereNull('sub_accounts.deleted_by')
          ->whereNull('sub_accounts.deleted_at');

        if($id != null){
            $sub_accounts_query->where('sub_accounts.id', $id);
        }
        elseif($name != null){
            $sub_accounts_query->where('sub_accounts.name', $name);
        }

        // return $schemes_query->get();

        return $sub_accounts_query->get()->map(function ($sub_account) {
            $sub_account_details = [
                'id' => $sub_account->id,
                'name' => $sub_account->name,
                'description' => $sub_account->description,
                'main_account_name' => $sub_account->mainAccount->name,
                'main_account_id' => $sub_account->mainAccount->id,
                'main_account_type' => $sub_account->mainAccount->type,

            ];

            $related_user =  CustomUserRelations::relatedUsersDetails($sub_account);

            return array_merge($sub_account_details, $related_user);
        });

    }
}
