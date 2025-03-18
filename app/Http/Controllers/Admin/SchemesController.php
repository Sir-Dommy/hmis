<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\Scheme;
use App\Models\Admin\SchemeTypes;
use App\Models\PaymentPath;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SchemesController extends Controller
{
   
    //soft Delete a scheme
    public function getAllSchemes(){        

        $all = Scheme::selectSchemes(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Scheme");

        return response()->json(
                $all ,200);
    }

    //soft Delete a scheme
    public function getSingleScheme(Request $request){   
        
        $reference = $request->id ?? $request->name;

        if($reference == null){
            throw new InputsValidationException("Either id or name should be provided");
        }

        $all = Scheme::selectSchemes($request->id, $request->name);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched Scheme with refrence: ".$reference);

        return response()->json(
                $all ,200);
    }
    
    //create a scheme
    public function createScheme(Request $request){
        $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'payment_path' => 'required|exists:payment_paths,name',
            'name' => 'required|string|min:3|max:255|unique:schemes',
            'account' => 'required|string|min:1|max:255|unique:schemes',
            'initiate_url' => 'string|min:3|max:255',
            'bill_url' => 'string|min:3|max:255',
            'authentication_url' => 'string|min:3|max:255',
            'validation_url' => 'string|min:3|max:255',
            'balance_url' => 'string|min:3|max:255',
            'bridge_balance_url' => 'string|min:3|max:255',
            'other_url' => 'string|min:3|max:255',
            'username' => 'string|min:3|max:255',
            'password' => 'string|min:3|max:255',
            'description' => 'string|max:1000',
            'scheme_types' => 'nullable|array',
        ]);

        try{
            DB::beginTransaction();

            $created = Scheme::create([
                'payment_type_id' => $request->payment_type_id,
                'name' => $request->name,
                'account' => $request->account,
                'initiate_url' => $request->initiate_url,
                'bill_url' => $request->bill_url,
                'authentication_url' => $request->authentication_url,
                'validation_url' => $request->validation_url,
                'balance_url' => $request->balance_url,
                'bridge_balance_url' => $request->bridge_balance_url,
                'other_url' => $request->other_url,
                'username' => $request->username,
                'password' => $request->password,
                'description' => $request->description,
                'payment_path_id' => $this->getPaymentPathId($request->payment_path),
                'created_by' => User::getLoggedInUserId()
            ]);
    
            // add related scheme types
            if(!$request->scheme_types){
                SchemeTypes::create([
                    "name" => "Default",
                    "Description" => "This is a default scheme type",
                    "scheme_id" => $created->id
                ]);
            }
    
            else{
                foreach ($request->scheme_types as $type){
                    $validator = Validator::make((array) $type, [
                        'name' => 'required|unique:scheme_types,name',
                        'max_visits_per_year' => 'nullable|numeric|min:0',
                        'max_amount_per_visit' => 'nullable|numeric|min:0',
                    ]);
    
                    if ($validator->fails()) {
                        return response()->json(['errors' => $validator->errors()], 422);
                    }
    
                    count(SchemeTypes::where('name', $type['name'])->where('scheme_id', $request->id)->get('id')) > 0 ? null :
                        SchemeTypes::create([
                            "name" => $type['name'],
                            "scheme_id" => $created->id,
                            'max_visits_per_year' => $type['max_visits_per_year'],
                            'max_amount_per_visit' => $type['max_amount_per_visit'],
                        ]);
                }
            }

            //commit transaction
            DB::commit();
        }

        catch(Exception $e){
            //rollback transaction
            DB::rollBack();

            throw new Exception($e);
        }
        

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Scheme with name: ". $request->name);

        return response()->json(
                Scheme::selectSchemes(null, $request->name)
            ,200);
    }

    //update a scheme
    public function updateScheme(Request $request){
        $request->validate([
            'id' => 'required|integer|min:1|exists:schemes,id',
            'payment_type_id' => 'required|exists:payment_types,id',
            'payment_path' => 'required|exists:payment_paths,name',
            'name' => 'required|string|min:3|max:255',
            'account' => 'required|string|min:1|max:255',
            'initiate_url' => 'string|min:3|max:255',
            'bill_url' => 'string|min:3|max:255',
            'authentication_url' => 'string|min:3|max:255',
            'validation_url' => 'string|min:3|max:255',
            'balance_url' => 'string|min:3|max:255',
            'bridge_balance_url' => 'string|min:3|max:255',
            'other_url' => 'string|min:3|max:255',
            'username' => 'string|min:3|max:255',
            'password' => 'string|min:3|max:255',
            'description' => 'string|max:1000',
            'scheme_types' => 'nullable|array',
        ]);

        $existing = Scheme::where('name', $request->name)
                        ->orWhere('account', $request->account)
                        ->whereNull('deleted_by')
                        ->get();
        
        // if(count($existing) == 0){
        //     throw new NotFoundException(APIConstants::NAME_SCHEME);
        // }

        if((count($existing) > 0 && $existing[0]['id'] != $request->id)){
            throw new AlreadyExistsException(APIConstants::NAME_SCHEME);
        }

        try{

            //begin transaction
            DB::beginTransaction();

            Scheme::where('id', $request->id)
            ->update([
                'payment_type_id' => $request->payment_type_id,
                'name' => $request->name,
                'account' => $request->account,
                'initiate_url' => $request->initiate_url,
                'bill_url' => $request->bill_url,
                'authentication_url' => $request->authentication_url,
                'validation_url' => $request->validation_url,
                'balance_url' => $request->balance_url,
                'bridge_balance_url' => $request->bridge_balance_url,
                'other_url' => $request->other_url,
                'username' => $request->username,
                'password' => $request->password,
                'description' => $request->description,
                'payment_path_id' => $this->getPaymentPathId($request->payment_path),
                'updated_by' => User::getLoggedInUserId()
            ]);

            // add related scheme types
            if($request->scheme_types){
                foreach ($request->scheme_types as $type){
                    $validator = Validator::make((array) $type, [
                        'id' => 'nullable:exists:scheme_types,id',
                        'name' => 'required',
                        'max_visits_per_year' => 'nullable|numeric|min:0',
                        'max_amount_per_visit' => 'nullable|numeric|min:0',
                    ]);

                    if ($validator->fails()) {
                        return response()->json(['errors' => $validator->errors()], 422);
                    }

                    $existing_scheme_type = SchemeTypes::where('name', $type['name'])->where('scheme_id', $request->id)->get('id');

                    count($existing_scheme_type) > 0 && !isset($type['id']) ? throw new InputsValidationException("Provide id for scheme type ". $type['name'] ." if you want to update it ") : null;

                    if(count($existing_scheme_type) > 0 && $existing_scheme_type[0]['id'] != $type['id']){
                        throw new InputsValidationException("Scheme type with name ".$type['name']." Already exists");
                    }

                    $type['id'] ? SchemeTypes::where('id', $type['id'])
                                    ->update([
                                        "name" => $type['name'],
                                        "scheme_id" => $request->id,
                                        'max_visits_per_year' => $type['max_visits_per_year'],
                                        'max_amount_per_visit' => $type['max_amount_per_visit'],
                                    ])
                                : 
                                SchemeTypes::create([
                                    "name" => $type['name'],
                                    "scheme_id" => $request->id,
                                    'max_visits_per_year' => $type['max_visits_per_year'],
                                    'max_amount_per_visit' => $type['max_amount_per_visit'],
                                ]);
                }
            }

            //commit transaction
            DB::commit();
        }
        catch(Exception $e){
            //rollback transaction
            DB::rollBack();

            // throw exception
            throw new Exception($e);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Scheme with name: ". $request->name);

        return response()->json(
                Scheme::selectSchemes(null, $request->name)
            ,200);
    }

    //approve a scheme
    public function approveScheme($id){
        

        $existing = Scheme::selectSchemes($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SCHEME);
        }

    
        Scheme::where('id', $id)
            ->update([
                'approved_by' => User::getLoggedInUserId(),
                'approved_at' => Carbon::now(),
                'disabled_by' => null,
                'disabled_at' => null
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a Scheme with id: ". $id);

        return response()->json(
                Scheme::selectSchemes($id, null)
            ,200);
    }

    //disable a scheme
    public function disableScheme($id){
        

        $existing = Scheme::selectSchemes($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SCHEME);
        }

    
        Scheme::where('id', $id)
            ->update([
                'approved_by' => null,
                'approved_at' => null,
                'disabled_by' => User::getLoggedInUserId(),
                'disabled_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_DISABLE, "Disabled a Scheme with id: ". $id);

        return response()->json(
                Scheme::selectSchemes($id, null)
            ,200);
    }

    //soft Delete a scheme
    public function softDeleteScheme($id){
        

        $existing = Scheme::selectSchemes($id, null);
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SCHEME);
        }

    
        Scheme::where('id', $id)
            ->update([
                'deleted_by' => User::getLoggedInUserId(),
                'deleted_at' => Carbon::now()
            ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Soft deleted a Scheme with id: ". $id);

        return response()->json(
                Scheme::selectSchemes($id, null)
            ,200);
    }

    //soft Delete a scheme
    public function permanentDeleteScheme($id){
        

        $existing = Scheme::where('id', $id)->get();
        
        if(count($existing) == 0){
            throw new NotFoundException(APIConstants::NAME_SCHEME);
        }

    
        Scheme::destroy($id);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Permanently deleted a Scheme with name: ". $existing[0]['name']);

        return response()->json(
                []
            ,200);
    }

    private function schemeTypeExits($name){
        if(count(SchemeTypes::where('name', $name)->get()) > 0 ){
            return 1;
        }
        return 0;
    }

    private function getPaymentPathId($payment_path_name){
        $existing = PaymentPath::where('name', $payment_path_name)->get();

        count($existing) < 0 ? throw new NotFoundException(APIConstants::NAME_PAYMENT_PATH) : null;

        return $existing[0]['id'];

    }
}
