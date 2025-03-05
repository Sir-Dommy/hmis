<?php

namespace App\Http\Controllers\Accounts;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Accounts\Units;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UnitsController extends Controller
{
    //get all unit
    public function getAllUnits(){        

        $all = Units::selectUnits(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all units");

        return response()->json(
                $all ,200);
    }

    //get a single unit
    public function getSingleUnit(Request $request){   
        
        $request->id == null && $request->name == null ? throw new InputsValidationException("Either id or name should be provided") : null;

        $all = Units::selectUnits($request->id, $request->name);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched unit with id: ".$all[0]['name']);

        return response()->json(
                $all ,200);
    }
    
    //create a unit
    public function createUnit(Request $request){
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'description' => 'nullable|string|max:255',
        ]);

    
        $created = Units::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a units with name: ". $request->name);

        return response()->json(
                Units::selectUnits(null, $request->name)
            ,200);
    }

    //update a unit
    public function updateUnit(Request $request){
        $request->validate([
            'id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $existing = Units::where('name', $request->name)->get();

        count($existing) > 0 && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_UNIT) : null ;

    
        Units::where('id', $request->id)
             ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Updated a unit with name: ". $request->name);

        return response()->json(
                Units::selectUnits(null, $request->name)
            ,200);

    }

    //approve a unit
    public function approveUnit($id){
        

        $existing = Units::selectUnits($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_UNIT);
        }

    
        Units::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now(),
                'disabled_by' => null,
                'disabled_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a units with id: ". $id);

        return response()->json(
                Units::selectUnits($id, null)
            ,200);
    }

    //disable a unit
    public function disableUnits($id){
        

        $existing = Units::selectUnits($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_UNIT);
        }

    
        Units::where('id', $id)
            ->update([
                'approved_by' => null,
                'approved_at' => null,
                'disabled_by' => User::getLoggedInUserId(),
                'disabled_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_DISABLE, "Disabled a unit with id: ". $id);

        return response()->json(
                Units::selectUnits($id, null)
            ,200);
    }

    //soft Delete a unit
    public function softDeleteUnit($id){
        

        $existing = Units::selectUnits($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_UNIT);
        }

    
        Units::where('id', $id)
            ->update([
                'deleted_by' => User::getLoggedInUserId(),
                'deleted_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Soft unit a with id: ". $id);

        return response()->json(
                Units::selectUnits($id, null)
            ,200);
    }

    // restore soft-Deleted a unit
    public function restoreSoftDeleteUnit($id){
        

        $existing = Units::where('id', $id)->whereNotNull('deleted_by')->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_UNIT);
        }

    
        Units::where('id', $id)
            ->update([
                'deleted_by' => null,
                'deleted_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored Soft deleted a unit with id: ". $id);

        return response()->json(
                Units::selectUnits($id, null)
            ,200);
    }

    //permanently Delete a unit
    public function permanentDeleteUnit($id){
        

        $existing = Units::where('id', $id)->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_UNIT);
        }

    
        Units::destroy($id);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Permanently deleted a unit with name: ". $existing[0]['name']);

        return response()->json(
                []
            ,200);
    }
}
