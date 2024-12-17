<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\DrugFormula;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DrugFormulationsController extends Controller
{
    //create
    public function createDrugFormula(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:drug_formulations,name',
            'formula' => 'required|string|min:1|max:255',
            'description'=>'string|min:1|max:255'            
        ]);


        DrugFormula::create([
            'name' => $request->name, 
            'formula' => $request->formula, 
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Drug formula with name: ". $request->name);

        return response()->json(
            DrugFormula::selectDrugFormulation(null, $request->name)
        ,200);

    }

    //update
    public function updateDrugFormula(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:drug_formulations,id',
            'name' => 'required|string|min:1|max:255',
            'formula' => 'required|string|min:1|max:255',
            'description'=>'string|min:1|max:255'
            
        ]);

        $existing = DrugFormula::selectDrugFormulation(null, $request->name);

        count($existing) > 0  && $existing[0]['id'] != $request->id ? throw new AlreadyExistsException(APIConstants::NAME_DRUG_FORMULATION) : null;


        DrugFormula::where('id', $request->id)
            ->update([
                'name' => $request->name,
                'formula' => $request->formula, 
                'description' => $request->description,
                'updated' => Carbon::now(),
                'updated_by' => User::getLoggedInUserId(),
                'approved_by' => null,
                'approved_at' => null
        ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Drug formula with name: ". $request->name);

        return response()->json(
            DrugFormula::selectDrugFormulation($request->id, null)
        ,200);

    }

    //     //Get one 
    public function getSingleDrugFormula(Request $request){

        $drug_formula = DrugFormula::selectDrugFormulation($request->id, $request->name);

        count($drug_formula) < 1 ? throw new NotFoundException(APIConstants::NAME_DRUG_FORMULATION) : null ;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a Drug formula with name: ". $drug_formula[0]['name']);

        return response()->json(
            $drug_formula
        ,200);
    }


    //getting all
    public function getAllDrugFormulas(){

        $drug_formulas = DrugFormula::selectDrugFormulation(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Drug formulas");

        return response()->json(
            $drug_formulas
        ,200);
    }

    //approve
    public function approveDrugFormula($id){

        count(DrugFormula::selectDrugFormulation($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_DRUG_FORMULATION) : null;

        DrugFormula::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved Drug formula with id: ".$id);

        return response()->json(
            DrugFormula::selectDrugFormulation($id, null)
        ,200);

    }

    //soft delete
    public function softDeleteDrugFormula($id){
            
        count(DrugFormula::selectDrugFormulation($id, null)) < 1 ? throw new NotFoundException(APIConstants::NAME_DRUG_FORMULATION) : null;
        
        DrugFormula::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a Drug formula with id: ". $id);

        return response()->json(
            []
        ,200);
    }

    //restore
    public function restoreSoftDeleted($id){ 
        
        count(DrugFormula::where('id', $id)->whereNotNull('deleted_by')->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_DRUG_FORMULATION) : null;
        
        DrugFormula::where('id', $id)
                ->update([
                    'approved_at' => null,
                    'approved_by' => null,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restored a Drug formula with id: ". $id);

        return response()->json(
            DrugFormula::selectDrugFormulation($id, null)
        ,200);
    }

    //permanently delete
    public function permanentlyDelete($id){
            
        count(DrugFormula::where('id', $id)->get()) < 1 ? throw new NotFoundException(APIConstants::NAME_DRUG_FORMULATION) : null;
        
        DrugFormula::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted Drug formula with id: ". $id);

        return response()->json(
            []
        ,200);
    }
}
