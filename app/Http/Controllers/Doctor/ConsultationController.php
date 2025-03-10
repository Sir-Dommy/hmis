<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    //create consultation
    public function createConsultation(Request $request){
        $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'consultation_type' => 'required|exists:consultation_types,name',
        ]);
    }
}
