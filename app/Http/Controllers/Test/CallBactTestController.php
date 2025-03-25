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

        $data2 = $request->body();
        //write to file in storage folder
        ///home/sirdommy/Documents/hmis/storage/callback/test/g_pay_collect.txt
        $file = storage_path('callback/test/g_pay_collect.txt');
        // file_put_contents($file, json_encode($data));
        file_put_contents($file, json_encode($data2));

        return response()->json($data);
    }
}
