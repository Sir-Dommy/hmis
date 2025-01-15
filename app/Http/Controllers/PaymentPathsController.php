<?php

namespace App\Http\Controllers;

use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\PaymentPath;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Illuminate\Http\Request;

class PaymentPathsController extends Controller
{
    //Getting a single payment path details 
    public function getSinglePaymentPath(Request $request){

        if($request->id == null && $request->name){
            throw new InputsValidationException("id or name required!");
        }

        $payment_path = PaymentPath::selectPaymentPaths($request->id, $request->name);

        if(count($payment_path) < 1){
            throw new NotFoundException(APIConstants::NAME_PAYMENT_TYPE);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a payment path with id: ". $payment_path[0]['id']);

        return response()->json(
            $payment_path
        ,200);
    }
    //getting all payment type Details
    public function getAllPaymentPaths(){

        $payment_paths = PaymentPath::selectPaymentPaths(null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all Payment paths");


        return response()->json(
            $payment_paths
        ,200);
    }
}
