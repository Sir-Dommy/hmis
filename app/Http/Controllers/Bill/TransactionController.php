<?php

namespace App\Http\Controllers\Bill;

use App\Http\Controllers\Controller;
use App\Models\Bill\Transaction;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    //receive cash payment for a service
    public function receiveCashPayment(Request $request){
        $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'amount' => 'required|numeric|min:0',
            'fee' => 'nullable|numeric|min:0',
            'initiation_time' => 'nullable|date|before_or_equal:today'
        ]);

        $create_transaction = Transaction::createTransaction($request->bill_id, null, null, null, null, $request->initiation_time, $request->amount, $request->fee, Carbon::now(), "SUCCESS", $request->reason);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Transaction with id: ". $create_transaction[0]['id']);
        
        return response()->json(
            $create_transaction
        ,200);

    }

    
}
