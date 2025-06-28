<?php

namespace App\Http\Controllers\Bill;

use App\Http\Controllers\Controller;
use App\Models\Bill\Bill;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Illuminate\Http\Request;

class BillController extends Controller
{

    public function selectBills(Request $request)
    {
        $bills = Bill::selectBills($request->id, $request->bill_reference, $request->status);
        
        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched bills with id: " . $request->id . " and bill_reference: " . $request->bill_reference);

        return response()->json($bills, 200);
    }

    //test mpesa payments
    public function testMpesaPayment(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|numeric',
            'amount' => 'required|numeric'
        ]);
        # access token from Sir Dommy app in daraja
        $consumerKey = 'qqfQd3AkvZ8lUpraxEAvevhoVVCGwb9ko65bAiGFEzbtQjoz'; //confidential consumer key from sandbox app (daraja)
        $consumerSecret = 'PWUjS9eRXqomPOTtuiCkqntqxTcAMpPB9T2DkU0Gx2fHvWOPoOOp1L7WfrtUkXDG'; // confidential consumer secret from sandbox app (daraja)

        # define the variales
        # provide the following details, found on test credentials on the developer account -- daraja
        $BusinessShortCode = '4149729'; //This is the sandbox business short code
        $Passkey = 'b1f55c61396f830bf978bf7e6484f3870d9e0fe255a0fdc5fc282a33ba28adb3';  
        
        /*
            This are your info, for
            $PartyA should be the ACTUAL clients phone number or your phone number, format 2547********
            $AccountRefference, it maybe invoice number, account number etc on production systems, but for test just put anything
            TransactionDesc can be anything, probably a better description of or the transaction
            $Amount this is the total invoiced amount, Any amount here will be 
            actually deducted from a clients side/your test phone number once the PIN has been entered to authorize the transaction. 
            for developer/test accounts, this money will be reversed automatically by midnight.
        */
        
        $PartyA = $request->phone_number; // This is your phone number, 
        $AccountReference = $request->reference;
        $TransactionDesc = 'Sir Dommy';
        $Amount = $request->amount;

        $PartyA ="254".substr($PartyA, -9);

        
        # Get the timestamp, format YYYYmmddhms -> 20181004151020
        $Timestamp = date('YmdHis');    
        
        # Get the base64 encoded string -> $password. The passkey is the M-PESA Public Key
        $Password = base64_encode($BusinessShortCode.$Passkey.$Timestamp);

        # header for access token
        $headers = ['Content-Type:application/json; charset=utf8'];

        //     # M-PESA endpoint urls
        // $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        // $initiate_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $access_token_url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $initiate_url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        # callback url
        $CallBackURL = $request->callback_url; // This is the url that will be called by safaricom once the payment is done

        $curl = curl_init($access_token_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_USERPWD, $consumerKey.':'.$consumerSecret);
        $result = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $result = json_decode($result);

        //check if result has access_token
        if (!isset($result->access_token)) {
            return response()->json([
                'error' => 'Unable to get access token',
                "result" => $result
            ], 500);
        }
        $access_token = $result->access_token; 
        
        curl_close($curl);

        return response()->json($access_token, 200);


        # header for stk push
        $stkheader = ['Content-Type:application/json','Authorization:Bearer '.$access_token];

        # initiating the transaction
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $initiate_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader); //setting custom header

        $curl_post_data = array(
            //Fill in the request parameters with valid values
            'BusinessShortCode' => $BusinessShortCode,
            'Password' => $Password,
            'Timestamp' => $Timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $Amount,
            'PartyA' => $PartyA,
            'PartyB' => $BusinessShortCode,
            'PhoneNumber' => $PartyA,
            'CallBackURL' => $CallBackURL,
            'AccountReference' => $AccountReference,
            'TransactionDesc' => $TransactionDesc
        );


        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);

        $response = json_decode($curl_response, true);
    

        return response()->json($response, 200);
    }

    //test mpesa payments
    public function testMpesaWithdrawal(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|numeric',
            'amount' => 'required|numeric'
        ]);
        # access token from Sir Dommy app in daraja
        $consumerKey = 'qqfQd3AkvZ8lUpraxEAvevhoVVCGwb9ko65bAiGFEzbtQjoz'; //confidential consumer key from sandbox app (daraja)
        $consumerSecret = 'PWUjS9eRXqomPOTtuiCkqntqxTcAMpPB9T2DkU0Gx2fHvWOPoOOp1L7WfrtUkXDG'; // confidential consumer secret from sandbox app (daraja)

        # define the variales
        # provide the following details, found on test credentials on the developer account -- daraja
        $BusinessShortCode = '4149729'; //This is the sandbox business short code
        $Passkey = 'b1f55c61396f830bf978bf7e6484f3870d9e0fe255a0fdc5fc282a33ba28adb3';  
        
        /*
            This are your info, for
            $PartyA should be the ACTUAL clients phone number or your phone number, format 2547********
            $AccountRefference, it maybe invoice number, account number etc on production systems, but for test just put anything
            TransactionDesc can be anything, probably a better description of or the transaction
            $Amount this is the total invoiced amount, Any amount here will be 
            actually deducted from a clients side/your test phone number once the PIN has been entered to authorize the transaction. 
            for developer/test accounts, this money will be reversed automatically by midnight.
        */
        
        $PartyA = $request->phone_number; // This is your phone number, 
        $AccountReference = $request->reference;
        $TransactionDesc = 'Sir Dommy';
        $Amount = $request->amount;

        $PartyA ="254".substr($PartyA,-9);
        
        # Get the timestamp, format YYYYmmddhms -> 20181004151020
        $Timestamp = date('YmdHis');    
        
        # Get the base64 encoded string -> $password. The passkey is the M-PESA Public Key
        $Password = base64_encode($BusinessShortCode.$Passkey.$Timestamp);

        $initator_name = "Hussein"; // This is the name of the initiator, it can be anything
        $iniator_password = "S/uU7h55VsRamVhVm4NAE7vydAcZpOgsXlAS3fGIk/I3KmbdCyMeNKopG/w9ydm4OBGf5j6jAzyzlt7NFVYOO51OU6qZkj+9T4h+D2RTElEG5+vkuduy43NkBE0u6k3bk2ZRa4Pmr1HRIN4delRCuI2ccjvvQsd/a0uWAqY9OWkYQMmtsaUEqlTEO5+PaO0qmcv7U9oEntjLqv4/eCHLytuxxF918uuNiPR4yX2BW11aPPJNduncqIKSOYJzNFmeCvcy8/yF2hKYV7hDN4N7RBvbjrBzcBjS/tK4qzeiQ2eNCbtOXkR9li6aoauBW8+jcT/myFlcCFTEkbJCg2d4sA=="; // This is the password of the initiator, it can be anything

        # header for access token
        $headers = ['Content-Type:application/json; charset=utf8'];

        //     # M-PESA endpoint urls
        // $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        // $initiate_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        // $initiate_url = 'https://sandbox.safaricom.co.ke/mpesa/b2c/v3/paymentrequest';
        $access_token_url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $initiate_url = 'https://api.safaricom.co.ke/mpesa/b2c/v3/paymentrequest';

        # callback url
        $CallBackURL = $request->callback_url; // This is the url that will be called by safaricom once the payment is done

        $curl = curl_init($access_token_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_USERPWD, $consumerKey.':'.$consumerSecret);
        $result = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $result = json_decode($result);

        //check if result has access_token
        if (!isset($result->access_token)) {
            return response()->json([
                'error' => 'Unable to get access token',
                "result" => $result
            ], 500);
        }
        $access_token = $result->access_token; 
        
        curl_close($curl);

        // return response()->json($access_token, 200);


        # header for stk push
        $stkheader = ['Content-Type:application/json','Authorization:Bearer '.$access_token];

        # initiating the transaction
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $initiate_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader); //setting custom header

        $curl_post_data = array(
            //Fill in the request parameters with valid values
            'OriginatorConversationID' => 'feb5e3f2-fbbc-4745-844c-ee37b546f627', // This is a unique id for the transaction, it can be anything
            'InitiatorName' => $initator_name,
            'SecurityCredential' => $iniator_password, // This is the password of the initiator, it can be anything
            'CommandID' => 'BusinessPayment', // This is the command id, it can be anything
            'Amount' => $Amount, // This is the amount to be withdrawn
            'PartyA' => $PartyA, // This is the phone number of the client, it should be in the format 2547********
            'PartyB' => $BusinessShortCode, // This is the business short code, it can be anything
            'Remarks' => 'No remarks', // This is the remarks for the transaction
            'QueueTimeOutURL' => $CallBackURL, // This is the url that will be called if the transaction times out
            'ResultURL' => $CallBackURL, // This is the url that will be called if the transaction is successful
            'Occassion' => 'No Occassion', // This is the
            
        );


        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);

        $response = json_decode($curl_response, true);
    

        return response()->json($response, 200);
    }
}
