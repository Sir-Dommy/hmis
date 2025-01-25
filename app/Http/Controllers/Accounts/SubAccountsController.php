<?php

namespace App\Http\Controllers\Accounts;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Accounts\SubAccounts;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubAccountsController extends Controller
{
    //get all sub account
    public function getAllSubAccounts(){        

        $all = SubAccounts::selectSubAccounts(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all sub Accounts");

        return response()->json(
                $all ,200);
    }

    //get a single sub account
    public function getSingleSubAccount(Request $request){   
        
        $request->id == null && $request->name == null ? throw new InputsValidationException("Either id or name should be provided") : null;

        $all = SubAccounts::selectSubAccounts($request->id, $request->name);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched sub Account with id: ".$all[0]['name']);

        return response()->json(
                $all ,200);
    }
    
    //create a sub account
    public function createSubAccount(Request $request){
        $request->validate([
            'name' => 'required|string|max:255|unique:sub_accounts,name',
            'main_account_id' => 'required|exists:main_accounts,id',
            'description' => 'nullable|string|max:255',
        ]);

    
        $created = SubAccounts::create([
            'name' => $request->name,
            'main_account_id' => $request->main_account_id,
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a sub account with name: ". $request->name);

        return response()->json(
                SubAccounts::selectSubAccounts(null, $request->name)
            ,200);
    }

    //update a sub account
    public function updateSubAccounts(Request $request){
        $request->validate([
            'id' => 'required|exists:sub_accounts,id',
            'name' => 'required|string|max:255',
            'main_account_id' => 'required|exists:main_accounts,id',
            'description' => 'nullable|string|max:255',
        ]);

        $existing = SubAccounts::where('name', $request->name)->get();

        count($existing) > 0 && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_SUB_ACCOUNT) : null ;

    
        SubAccounts::where('id', $request->id)
             ->update([
                'name' => $request->name,
                'main_account_id' => $request->main_account_id,
                'description' => $request->description,
                'updated_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Updated a Sub account with name: ". $request->name);

        return response()->json(
                SubAccounts::selectSubAccounts(null, $request->name)
            ,200);

    }

    //approve a sub account
    public function approveSubAccount($id){
        

        $existing = SubAccounts::selectSubAccounts($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SUB_ACCOUNT);
        }

    
        SubAccounts::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now(),
                'disabled_by' => null,
                'disabled_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a sub account with id: ". $id);

        return response()->json(
                SubAccounts::selectSubAccounts($id, null)
            ,200);
    }

    //disable a sub accounts
    public function disableSubAccount($id){
        

        $existing = SubAccounts::selectSubAccounts($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SUB_ACCOUNT);
        }

    
        SubAccounts::where('id', $id)
            ->update([
                'approved_by' => null,
                'approved_at' => null,
                'disabled_by' => User::getLoggedInUserId(),
                'disabled_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_DISABLE, "Disabled a Sub account with id: ". $id);

        return response()->json(
                SubAccounts::selectSubAccounts($id, null)
            ,200);
    }

    //soft Delete a sub account
    public function softDeleteSubAccount($id){
        

        $existing = SubAccounts::selectSubAccounts($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SUB_ACCOUNT);
        }

    
        SubAccounts::where('id', $id)
            ->update([
                'deleted_by' => User::getLoggedInUserId(),
                'deleted_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Soft sub account a with id: ". $id);

        return response()->json(
                SubAccounts::selectSubAccounts($id, null)
            ,200);
    }

    // restore soft-Deleted a sub account
    public function restoreSoftDeleteSubAccount($id){
        

        $existing = SubAccounts::where('id', $id)->whereNotNull('deleted_by')->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SUB_ACCOUNT);
        }

    
        SubAccounts::where('id', $id)
            ->update([
                'deleted_by' => null,
                'deleted_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored Soft deleted a sub account with id: ". $id);

        return response()->json(
                SubAccounts::selectSubAccounts($id, null)
            ,200);
    }

    //permanently Delete a sub accounts
    public function permanentDeleteSubAccount($id){
        

        $existing = SubAccounts::where('id', $id)->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SUB_ACCOUNT);
        }

    
        SubAccounts::destroy($id);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Permanently deleted a Sub account with name: ". $existing[0]['name']);

        return response()->json(
                []
            ,200);
    }
}
