<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CallBactTestController extends Controller
{
    //receive data from url
    public function collect(Request $request)
    {
        $data = $request->all();

        //get ip from request
        $ip = $request->ip();

        //add ip to data
        $data['ip'] = $ip;

        //add current time to data
        $data['callback_time'] = date('Y-m-d H:i:s');

        //write to file in storage folder
        ///home/sirdommy/Documents/hmis/storage/callback/test/g_pay_collect.txt
        $file = storage_path('callback/test/g_pay_collect.txt');
        file_put_contents($file, json_encode($data), FILE_APPEND);

        // append on a new line
        file_put_contents($file, "\n", FILE_APPEND);

        // append on a new line
        file_put_contents($file, "\n", FILE_APPEND);


        return response()->json($data);
    }

    //receive data from url
    public function disburse(Request $request)
    {
        $data = $request->all();

        //get ip from request
        $ip = $request->ip();

        //add ip to data
        $data['ip'] = $ip;

        // no change

        //add current time to data
        $data['callback_time'] = date('Y-m-d H:i:s');

        //write to file in storage folder
        ///home/sirdommy/Documents/hmis/storage/callback/test/g_pay_collect.txt
        $file = storage_path('callback/test/g_pay_disburse.txt');
        
        file_put_contents($file, json_encode($data), FILE_APPEND);

        // append on a new line
        file_put_contents($file, "\n", FILE_APPEND);

        // append on a new line
        file_put_contents($file, "\n", FILE_APPEND);

        return response()->json($data);
    }
}
