<?php

namespace App\Http\Controllers\Bill;

use App\Exceptions\InputsValidationException;
use App\Http\Controllers\Controller;
use App\Models\Bill\BillItem;
use App\Models\Bill\Transaction;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    //receive cash payment for a service
    public function clearBillUsingCashPayment(Request $request){
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

    //receive cash payment for a service
    public function payForSpecificBillItemsUsingCash(Request $request){
        $request->validate([
            'bill_item_details' => 'required',
        ]);

        foreach ($request->bill_item_details as $bill_item_detail) {
            $validator = Validator::make((array) $bill_item_detail, [            
                'bill_item_id' => 'required|exists:bill_items,id',
                'amount' => 'required|numeric|min:0',
                'fee' => 'nullable|numeric|min:0',
                'initiation_time' => 'nullable|date|before_or_equal:today'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $existing_bill_item = BillItem::selectBillItems($bill_item_detail->bill_item_id);

        }


        $create_transaction = Transaction::createTransaction($request->bill_id, null, null, null, null, $request->initiation_time, $request->amount, $request->fee, Carbon::now(), "SUCCESS", $request->reason);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Transaction with id: ". $create_transaction[0]['id']);
        
        return response()->json(
            $create_transaction
        ,200);

    }

    public function searchTransaction(Request $request){
        $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'amount' => 'required|numeric|min:0',
            'fee' => 'nullable|numeric|min:0',
            'initiation_time' => 'nullable|date|before_or_equal:today'
        ]);
    }

    // reverse cash payment for for a service
    public function reverseCashPayment(Request $request){
        $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'amount' => 'required|numeric|min:0',
            'fee' => 'nullable|numeric|min:0',
            'initiation_time' => 'nullable|date|before_or_equal:today'
        ]);
    }

    
}
