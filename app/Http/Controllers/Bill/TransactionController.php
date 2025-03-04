<?php

namespace App\Http\Controllers\Bill;

use App\Exceptions\InputsValidationException;
use App\Http\Controllers\Controller;
use App\Models\Bill\Bill;
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
            'amount' => 'required|numeric|min:1',
            'fee' => 'nullable|numeric|min:0',
            'initiation_time' => 'nullable|date|before_or_equal:today'
        ]);        

        $create_transaction = Transaction::createTransaction($request->bill_id, null, null, null, null, $request->initiation_time, $request->amount, $request->fee, Carbon::now(), "SUCCESS", $request->reason);

        $this->autoCompleteTransactionIfBillIsCleared($request->bill_id);

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
                'amount' => 'required|numeric|min:1',
                'fee' => 'nullable|numeric|min:0',
                'initiation_time' => 'nullable|date|before_or_equal:today'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            echo $bill_item_detail['bill_item_id'];
            $existing_bill_item = BillItem::selectBillItems($bill_item_detail->bill_item_id);

            // ensure only one bill item exists!
            Transaction::ensureSingleInstance($existing_bill_item, "No unique bill item with id: ".$bill_item_detail->bill_item_id ." contact admin for help");

            $existing_bill_item[0]['amount_paid'] >= ($existing_bill_item[0]['one_item_selling_price'] - $existing_bill_item[0]['discount']) * $existing_bill_item[0]['quantity'] ? throw new InputsValidationException("Bill item id: ".$bill_item_detail->bill_item_id. " Already paid in full! remove it from list") : null;

            if(($existing_bill_item[0]['one_item_selling_price'] - $existing_bill_item[0]['discount']) * $existing_bill_item[0]['quantity'] <= $existing_bill_item[0]['amount_paid'] + $bill_item_detail->amount){
                BillItem::where('id', $bill_item_detail->bill_item_id)
                        ->update([
                            'amount_paid' => $existing_bill_item[0]['amount_paid'] + $bill_item_detail->amount,
                            'status' => APIConstants::STATUS_SUCCESS
                        ]);

                $this->autoCompleteTransactionIfBillIsCleared($existing_bill_item[0]['bill_id']);
            }
            else{
                BillItem::where('id', $bill_item_detail->bill_item_id)
                        ->update([
                            'amount_paid' => $existing_bill_item[0]['amount_paid'] + $bill_item_detail->amount
                        ]);
            }



            $create_transaction = Transaction::createTransaction($request->bill_id, null, null, null, null, $request->initiation_time, $request->amount, $request->fee, Carbon::now(), "SUCCESS", $request->reason);

            UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a Transaction with id: ". $create_transaction[0]['id']);
        

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



    // auto complete or clear bill .... only after amount paid is greater than or equal to bill amount
    private function autoCompleteTransactionIfBillIsCleared($bill_id){
        $existing_bill = Bill::where('id', $bill_id)->get();

        count($existing_bill) != 1 ? throw new InputsValidationException("Cannot clear bill! No unique bill found! Contact admin for help") : null;

        $existing_bill[0]['status'] == APIConstants::STATUS_SUCCESS ? throw new InputsValidationException("Bill already paid in full!") : null;

        $amount_payable = $existing_bill[0]['bill_amount'] - $existing_bill[0]['discount'];

        $transactions_amounts_total = 0.0;

        // get all existing transactions
        $transactions = Transaction::where('bill_id', $bill_id)->get();

        foreach($transactions as $transaction){
            $transactions_amounts_total += ($transaction['amount'] - $transaction['fee']);
        }

        if($transactions_amounts_total >= $amount_payable){
            // update all bill items status to success (paid)... first select existing bills
            $existing_bill_items = BillItem::where('bill_id', $bill_id)->get();

            $amount_payable_items = 0.0;

            //loop through the bill items and update amount paid to bill item amount minus transaction and update to success
            foreach($existing_bill_items as $bill_item){

                $amount_payable_items += (($bill_item['one_item_selling_price'] - $bill_item['discount']) * $bill_item['quantity']);

                // if $amount payable is greater than bill amount update status only ..... no need of having item paid yet amount paid is less than item price
                if($amount_payable_items > $amount_payable){
                    BillItem::where('id', $bill_item->id)
                        ->update([
                            'status' => APIConstants::STATUS_SUCCESS
                        ]);

                    continue;
                }

                // no need of updating bill item if its already fully paid (IN SUCCESS STATUS)
                if($bill_item->status == APIConstants::STATUS_SUCCESS){
                    continue;
                }
                
                // update bill item amount paid and status to success
                BillItem::where('id', $bill_item->id)
                    ->update([
                        'amount_paid' => (($bill_item['one_item_selling_price'] - $bill_item['discount']) * $bill_item['quantity']),
                        'status' => APIConstants::STATUS_SUCCESS
                    ]);

            }

            Bill::where('id', $bill_id)
                ->update([
                    'status' => APIConstants::STATUS_SUCCESS
                ]);

        }
    }

    
}
