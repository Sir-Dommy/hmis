<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Admin\ConsultationType;
use App\Models\Doctor\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationController extends Controller
{
    //create consultation
    public function createConsultation(Request $request){
        $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'consultation_type' => 'required|exists:consultation_types,name',
        ]);

        $consultation_type_id = ConsultationType::where('name', $request->consultation_type)->first()->id;

        // DB::beginTransaction();

        // $created = Consultation::create([
        //     'visit_id'=>$request->visit_id,
        //     'consultation_type_id'=>R
        // ]);

        return response()->json(
            $consultation_type_id, 200
        );
    }
}
