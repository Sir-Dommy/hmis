<?php

namespace App\Http\Controllers\Bill;

use App\Http\Controllers\Controller;
use App\Models\Bill\Transaction;
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

        Transaction::createTransaction($request->bill_id, null, null, null, null, $request->initiation_time, $request->amount, $request->fee, null, "SUCCESS", $request->reason);
    }

    
}
