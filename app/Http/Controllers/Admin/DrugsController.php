<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Admin\ServiceRelated\ServiceController;
use App\Http\Controllers\Controller;
use App\Models\Admin\Brand;
use App\Models\Admin\Drug;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\CountValidator\Exact;

class DrugsController extends Controller
{
    //create
    public function createDrug(Request $request){
        $request->validate([
            "brand" => 'string|min:1|max:255|exists:brands,name',
            "name" => 'required|string|min:1|max:255|unique:drugs,name',
            "amount_in_stock" => 'required|integer|min:0',
            "price_per_item" => 'required|numeric|min:0',
            "description" =>'string|min:1|max:255',
            "expiry_date" =>'required|date|after_or_equal:today',
        ]);

        $brand = Brand::selectBrands(null, $request->brand);

        try{
            //begin transaction
            DB::beginTransaction();

            Drug::create([
                'name' => $request->name, 
                "brand_id" => $brand[0]['id'],
                "amount_in_stock" => $request->amount_in_stock,
                "price_per_item" => $request->price_per_item,
                "description" => $request->description,
                "expiry_date" => $request->expiry_date,
                'created_by' => User::getLoggedInUserId()
            ]);
    
            $service_controller = app()->make(\App\Http\Controllers\Admin\ServiceRelated\ServiceController::class);
    
            $request->merge([
                "service" => $request->name,
                // "brand" => $request->brand,
                "service_price_affected_by_time" => false
            ]);
    
            $this->createService($service_controller, $request);
    
    
            // create service prices
            $service_price_controller = app()->make(\App\Http\Controllers\Admin\ServiceRelated\ServicePriceController::class);
    
            $request->merge([            
                'category' => 'Drug',
                'drug' => $request->name,
                'cost_price' => $request->price_per_item,
            ]);
    
            $service_price_controller->createServicePrice($request);

            
            // commit transaction
            DB::commit();

        }

        catch(Exception $e){
            // rollback transaction
            DB::rollback();

            throw new Exception($e);
        }
        
        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Drug with name: ". $request->name);

        return response()->json(
            Drug::selectDrug(null, $request->name)
        ,200);

    }

    //update
    public function updateDrug(Request $request){
        $request->validate([
            "id" => 'required|integer|exists:drugs,id',
            "brand" => 'string|min:1|max:255|exists:brands,name',
            "name" => 'required|string|min:1|max:255',
            "in_stock" => 'required|integer|min:0',
            "price_per_item" => 'required|numeric|min:0',
            "description" =>'string|min:1|max:255',
            "expiry_date" =>'required|date',
            
        ]);

        $existing = Drug::selectDrug(null, $request->name);

        count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_DRUG) : null;

        $brand = Brand::selectBrands(null, $request->brand);

        Drug::where('id', $request->id)
            ->update([
                'name' => $request->name, 
                "brand_id" => $brand[0]['id'],
                "in_stock" => $request->in_stock,
                "price_per_item" => $request->price_per_item,
                "description" => $request->description,
                "expiry_date" => $request->expiry_date,
                'updated_at' => Carbon::now(),
                'updated_by' => User::getLoggedInUserId(),
                'approved_by' => null,
                'approved_at' => null
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Drug with name: ". $request->name);

        return response()->json(
            Drug::selectDrug($request->id, null)
        ,200);

    }

    //     //Get one 
    public function getSingleDrug(Request $request){

        $drug = Drug::selectDrug($request->id, $request->name);

        count($drug) < 1 ? throw new NotFoundException(APIConstants::NAME_DRUG) : null ;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a Drug with name: ". $drug[0]['name']);

        return response()->json(
            $drug
        ,200);
    }


    //getting all
    public function getAllDrugs(){

        $drug = Drug::selectDrug(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Drugs");

        return response()->json(
            $drug
        ,200);
    }

    //approve
    public function approveDrug($id){

        count(Drug::selectDrug($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_DRUG) : null;

        Drug::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved Drug with id: ".$id);

        return response()->json(
            Drug::selectDrug($id, null)
        ,200);

    }

    //soft delete
    public function softDeleteDrug($id){
            
        count(Drug::selectDrug($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_DRUG) : null;
        
        Drug::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a Drug with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeleted($id){ 
        
        count(Drug::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_DRUG) : null;
        
        Drug::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored a Drug with id: ". $id);

        return response()->json(
            Drug::selectDrug($id, null)
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(Drug::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_DRUG) : null;
        
        Drug::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted Drug with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    private function createService(ServiceController $service_controller, Request $request){
        
        $service_controller->createService($request);
    }
}
