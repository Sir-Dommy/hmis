<?php

namespace App\Http\Controllers\Doctor;

use App\Exceptions\InputsValidationException;
use App\Http\Controllers\Controller;
use App\Models\Admin\ConsultationType;
use App\Models\Admin\PhysicalExaminationType;
use App\Models\Admin\Symptom;
use App\Models\Doctor\Consultation;
use App\Models\Doctor\ConsultationPhysicalExaminationsJoin;
use App\Models\Doctor\ConsultationSymptomsJoin;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
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

            $created = Consultation::create([
                'visit_id'=>$request->visit_id,
                'consultation_type_id'=>$consultation_type_id,
                'clinical_history'=>$request->clinical_history,
                'created_by'=> User::getLoggedInUserId()
            ]);

            // create chief complains
            foreach($request->chief_complains as $chief_complain){
                
                $existing_chief_complain = Symptom::where('name', $chief_complain)->get('id');

                count($existing_chief_complain) < 1 ? throw new InputsValidationException("Chief complain with name: " . $chief_complain . " does not exist!!!") : null;

                ConsultationSymptomsJoin::create([
                    'consultation_id' => $created->id,
                    'symptom_id' => $existing_chief_complain[0]['id'],
                ]);

                
            }
            // create chief physical examinations
            foreach($request->physical_examinations as $physical_examination){
                
                $existing_physical_examination = PhysicalExaminationType::where('name', $physical_examination)->get('id');

                count($existing_physical_examination) < 1 ? throw new InputsValidationException("Physical examination with name: " . $physical_examination . " does not exist!!!") : null;

                ConsultationPhysicalExaminationsJoin::create([
                    'consultation_id' => $created->id,
                    'physical_examination_id' => $existing_physical_examination[0]['id'],
                ]);
                
            }


            //commit transaction if there are no errors
            DB::commit();
        }

        catch(Exception $e){
            //rollback the transaction
            DB::rollBack();

            throw new Exception($e);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a consultation: ". $created->id);

        return response()->json(
            Consultation::selectConsultations($created->id)
        ,200);
    }
}
