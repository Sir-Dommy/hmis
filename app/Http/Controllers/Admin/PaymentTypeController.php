<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin\PaymentType;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    //
    //saving a new payment type
    public function createPaymentType(Request $request){
        $request->validate([
            'name' => 'required|string|min:1|max:255|unique:payment_types',
            'description'=>'string|min:2|max:255'
            
        ]);        

        PaymentType::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => User::getLoggedInUserId()
        ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a payment type with name: ". $request->name);

        return response()->json(
            PaymentType::selectPaymentTypes(null, $request->name)
        ,200);

    }

   // updating a payment type
    public function updatePaymentType(Request $request){
        $request->validate([
            'id' => 'required|integer|min:0|exists:payment_types,id',
            'name' => 'required|string|min:1|max:255',
            'description' => 'string|min:1|max:255' 
        ]);

        $existing = PaymentType::selectPaymentTypes(null, $request->name);

        if(count($existing) > 0 && $existing[0]["id"] != $request->id){
            throw new AlreadyExistsException(APIConstants::NAME_PAYMENT_TYPE. " ". $request->email);
        }
        

        PaymentType::where('id', $request->id)
                ->update([
                    'name' => $request->name, 
                    'description' => $request->description,
                    'updated_by' => User::getLoggedInUserId()
                ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated a Payment type with id: ". $request->id);
        

        return response()->json(
            PaymentType::selectPaymentTypes($request->id, null)
            ,200);

    }
    //Gettind a single payment type details 
    public function getSinglePaymentType(Request $request){

        if($request->id == null && $request->name){
            throw new InputsValidationException("id or name required!");
        }

        $payment_type = PaymentType::selectPaymentTypes($request->id, $request->name);

        if(count($payment_type) < 1){
            throw new NotFoundException(APIConstants::NAME_PAYMENT_TYPE);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a payment type with id: ". $payment_type[0]['id']);

        return response()->json(
            $payment_type
        ,200);
    }
    //getting all payment type Details
    public function getAllPaymentTypes(){

        $payment_types = PaymentType::selectPaymentTypes(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Payment types");


        return response()->json(
            $payment_types
        ,200);
    }

    //approving a payment type
    public function approvePaymentType($id){
            
        $existing = PaymentType::selectPaymentTypes($id, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_PAYMENT_TYPE. " with id: ". $id);
        }

        PaymentType::where('id', $id)
                ->update([
                    'approved_by' => User::getLoggedInUserId(),  
                    'approved_at' => Carbon::now(),
                ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved a payment type with id : ". $id);

        return response()->json(
            PaymentType::selectPaymentTypes($id, null)
        ,200);
    }

    public function softDelete($id){
            
        $existing = PaymentType::selectPaymentTypes($id, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_PAYMENT_TYPE. " with id: ". $id);
        }
        
        PaymentType::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a payment type with id: ". $id);

        return response()->json(
            PaymentType::selectPaymentTypes($id, null)
        ,200);
    }

    public function permanentlyDelete($id){
            
        $existing = PaymentType::where('id',$id)->get();

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_PAYMENT_TYPE. " with id: ". $id);
        }
        
        PaymentType::destroy($id);


        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Deleted a payment type with id: ". $id);

        return response()->json(
            []
        ,200);
    }
}
