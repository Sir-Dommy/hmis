<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Admin\ConsultationType;
use App\Models\Doctor\Consultation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConsultationController extends Controller
{
    //create consultation
    public function createConsultation(Request $request){
        $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'consultation_type' => 'required|exists:consultation_types,name',
            'clinical_history' => 'nullable',
            'chief_complains' => 'required',
            'physical_examinations' => 'required',
        ]);

        $consultation_type_id = ConsultationType::where('name', $request->consultation_type)->get('id')[0]['id'];

        try{

            DB::beginTransaction();

            // $created = Consultation::create([
            //     'visit_id'=>$request->visit_id,
            //     'consultation_type_id'=>$request->$consultation_type_id,
            //     'clinical_history'=>$request->clinical_history
            // ]);

            // create chief complains
            foreach($request->chief_complains as $chief_complain){
                        

                return $chief_complain;
                
            }


            //commit transaction if there are no errors
            DB::commit();
        }

        catch(Exception $e){
            //rollback the transaction
            DB::rollBack();

            throw new Exception($e);
        }

        return response()->json(
            $consultation_type_id, 200
        );
    }
}
