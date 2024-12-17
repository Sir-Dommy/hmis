<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\Brand;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    //create
    public function createBrand(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:brands,name',
            'company' => 'required|string|min:1|max:255',
            'description'=>'string|min:1|max:255'            
        ]);


        Brand::create([
            'name' => $request->name,
            'company' => $request->company, 
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Brand with name: ". $request->name);

        return response()->json(
            Brand::selectBrands(null, $request->name)
        ,200);

    }

    //update
    public function updateBrand(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:brands,id',
            'name' => 'required|string|min:1|max:255',
            'company' => 'required|string|min:1|max:255',
            'description'=>'string|min:1|max:255'
            
        ]);

        $existing = Brand::selectBrands(null, $request->name);

        count($existing) > 0 ?? $existing[0]['id'] != $request->id ?? throw new AlreadyExistsException(APIConstants::NAME_BRAND);


        Brand::where('id', $request->id)
            ->update([
                'name' => $request->name,
                'company' => $request->company, 
                'description' => $request->description,
                'updated_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Brand with name: ". $request->name);

        return response()->json(
            Brand::selectBrands($request->id, null)
        ,200);

    }

    //     //Get one 
    public function getSingleBrand(Request $request){

        $brand = Brand::selectBrands($request->id, $request->name);

        if(count($brand) < 1){
            throw new NotFoundException(APIConstants::NAME_BRAND);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched an brand with name: ". $brand[0]['name']);

        return response()->json(
            $brand
        ,200);
    }


    //getting all
    public function getAllBrands(){

        $brands = Brand::selectBrands(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all brands");

        return response()->json(
            $brands
        ,200);
    }

    //approve
    public function approveBrand($id){

        count(Brand::selectBrands($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_BRAND) : null;

        Brand::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved brand with id: ".$id);

        return response()->json(
            Brand::selectBrands($id, null)
        ,200);

    }

    //soft delete
    public function softDeleteBrand($id){
            
        
        count(Brand::selectBrands($id, null)) < 1 ?? throw new NotFoundException(APIConstants::NAME_BRAND);
        
        Brand::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a brand with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeletedBrand($id){ 
        
        count(Brand::where('id', $id)->get()) < 1 ?? throw new NotFoundException(APIConstants::NAME_BRAND);
        
        Brand::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a brand with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(Brand::where('id', $id)->get()) < 1 ?? throw new NotFoundException(APIConstants::NAME_BRAND);
        
        Brand::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted brand with id: ". $id);

        return response()->json(
            []
        ,200);
    }
}
