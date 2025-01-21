<?php

namespace App\Http\Controllers\Admin\ServiceRelated;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\ServiceRelated\Service;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    //get all services
    public function getAllServices(){        

        $all = Service::selectServices(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Services");

        return response()->json(
                $all ,200);
    }

    //get a single service
    public function getSingleService(Request $request){   
        
        $request->id == null && $request->name == null ? throw new InputsValidationException("Either id or name should be provided") : null;

        $all = Service::selectServices($request->id, $request->name);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched Service with id: ".$all[0]['id']);

        return response()->json(
                $all ,200);
    }
    
    //create a service
    public function createService(Request $request){
        $request->validate([
            'name' => 'required|string|unique:services,name',
            'description' => 'nullable|string|max:1000',
        ]);

    
        $created = Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Service with name: ". $request->name);

        return response()->json(
                Service::selectServices(null, $request->name)
            ,200);
    }

    //update a service
    public function updateService(Request $request){
        $request->validate([
            'id' => 'required|exists:services,id',
            'name' => 'required|string',
            'description' => 'nullable|string|max:1000',
        ]);

        $existing = Service::where('name', $request->name)->get();

        count($existing) > 0 && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_SERVICE) : null ;

    
        Service::where('id', $request->id)
             ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Updated a Service with name: ". $request->name);

        return response()->json(
                Service::selectServices(null, $request->name)
            ,200);

    }

    //approve a service
    public function approveService($id){
        

        $existing = Service::selectServices($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE);
        }

    
        Service::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now(),
                'disabled_by' => null,
                'disabled_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a Service with id: ". $id);

        return response()->json(
                Service::selectServices($id, null)
            ,200);
    }

    //disable a service
    public function disableService($id){
        

        $existing = Service::selectServices($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE);
        }

    
        Service::where('id', $id)
            ->update([
                'approved_by' => null,
                'approved_at' => null,
                'disabled_by' => User::getLoggedInUserId(),
                'disabled_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_DISABLE, "Disabled a Service with id: ". $id);

        return response()->json(
                Service::selectServices($id, null)
            ,200);
    }

    //soft Delete a service
    public function softDeleteService($id){
        

        $existing = Service::selectServices($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE);
        }

    
        Service::where('id', $id)
            ->update([
                'deleted_by' => User::getLoggedInUserId(),
                'deleted_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Soft deleted a service with id: ". $id);

        return response()->json(
                Service::selectServices($id, null)
            ,200);
    }

    // restore soft-Deleted a service
    public function restoreSoftDeleteService($id){
        

        $existing = Service::where('id', $id)->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE);
        }

    
        Service::where('id', $id)
            ->update([
                'deleted_by' => null,
                'deleted_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored Soft deleted a service with id: ". $id);

        return response()->json(
                Service::selectServices($id, null)
            ,200);
    }

    //permanently Delete a service
    public function permanentDeleteService($id){
        

        $existing = Service::where('id', $id)->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SERVICE);
        }

    
        Service::destroy($id);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Permanently deleted a Service with name: ". $existing[0]['name']);

        return response()->json(
                []
            ,200);
    }

}
