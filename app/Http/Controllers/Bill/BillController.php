<?php

namespace App\Http\Controllers\Bill;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BillController extends Controller
{
    //test mpesa payments
    public function testMpesaPayment(Request $request)
    {
        $data = [
            'amount' => 1,
            'phone_number' => '254708123456',
            'account_number' => '123456',
            'transaction_type' => 'CustomerPayBillOnline',
            'transaction_desc' => 'Test payment',
            'callback_url' => url('/api/v1/bill/callback'),
        ];

        return response()->json($data);
    }
}
