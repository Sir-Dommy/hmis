<?php

namespace App\Http\Controllers\Accounts;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Accounts\MainAccounts;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MainAccountsController extends Controller
{
    //get all main accounts
    public function getAllMainAccounts(){        

        $all = MainAccounts::selectMainAccounts(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Main Accounts");

        return response()->json(
                $all ,200);
    }

    //get a single main accounts
    public function getSingleMainAccount(Request $request){   
        
        $request->id == null && $request->name == null ? throw new InputsValidationException("Either id or name should be provided") : null;

        $all = MainAccounts::selectMainAccounts($request->id, $request->name);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched Main Account with id: ".$all[0]['name']);

        return response()->json(
                $all ,200);
    }
    
    //create a main account
    public function createMainAccount(Request $request){
        $request->validate([
            'name' => 'required|string|max:255|unique:main_accounts,name',
            'type' => 'required|string|in:Cr,Dr',
            'description' => 'nullable|string|max:255',
        ]);

    
        $created = MainAccounts::create([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Main account with name: ". $request->name);

        return response()->json(
                MainAccounts::selectMainAccounts(null, $request->name)
            ,200);
    }

    //update a main account
    public function updateMainAccounts(Request $request){
        $request->validate([
            'id' => 'required|exists:main_accounts,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Cr,Dr',
            'description' => 'nullable|string|max:255',
        ]);

        $existing = MainAccounts::where('name', $request->name)->get();

        count($existing) > 0 && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_MAIN_ACCOUNT) : null ;

    
        MainAccounts::where('id', $request->id)
             ->update([
                'name' => $request->name,
                'type' => $request->type,
                'description' => $request->description,
                'updated_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Updated a Main account with name: ". $request->name);

        return response()->json(
                MainAccounts::selectMainAccounts(null, $request->name)
            ,200);

    }

    //approve a main account
    public function approveMainAccount($id){
        

        $existing = MainAccounts::selectMainAccounts($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_MAIN_ACCOUNT);
        }

    
        MainAccounts::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now(),
                'disabled_by' => null,
                'disabled_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a main account with id: ". $id);

        return response()->json(
                MainAccounts::selectMainAccounts($id, null)
            ,200);
    }

    //disable a main account
    public function disableMainAccount($id){
        

        $existing = MainAccounts::selectMainAccounts($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_MAIN_ACCOUNT);
        }

    
        MainAccounts::where('id', $id)
            ->update([
                'approved_by' => null,
                'approved_at' => null,
                'disabled_by' => User::getLoggedInUserId(),
                'disabled_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_DISABLE, "Disabled a Main account with id: ". $id);

        return response()->json(
                MainAccounts::selectMainAccounts($id, null)
            ,200);
    }

    //soft Delete a main account
    public function softDeleteMainAccount($id){
        

        $existing = MainAccounts::selectMainAccounts($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_MAIN_ACCOUNT);
        }

    
        MainAccounts::where('id', $id)
            ->update([
                'deleted_by' => User::getLoggedInUserId(),
                'deleted_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Soft main account with id: ". $id);

        return response()->json(
                MainAccounts::selectMainAccounts($id, null)
            ,200);
    }

    // restore soft-Deleted a main account
    public function restoreSoftDeleteMainAccount($id){
        

        $existing = MainAccounts::where('id', $id)->whereNotNull('deleted_by')->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_MAIN_ACCOUNT);
        }

    
        MainAccounts::where('id', $id)
            ->update([
                'deleted_by' => null,
                'deleted_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored Soft deleted a main account with id: ". $id);

        return response()->json(
                MainAccounts::selectMainAccounts($id, null)
            ,200);
    }

    //permanently Delete a main account
    public function permanentDeleteMainAccount($id){
        

        $existing = MainAccounts::where('id', $id)->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_MAIN_ACCOUNT);
        }

    
        MainAccounts::destroy($id);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Permanently deleted a Main account with name: ". $existing[0]['name']);

        return response()->json(
                []
            ,200);
    }

}
