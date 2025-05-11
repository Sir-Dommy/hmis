<?php

namespace App\Http\Controllers\Doctor;

use App\Exceptions\InputsValidationException;
use App\Http\Controllers\Controller;
use App\Models\Admin\ConsultationType;
use App\Models\Admin\Diagnosis;
use App\Models\Admin\PhysicalExaminationType;
use App\Models\Admin\Symptom;
use App\Models\Doctor\Consultation;
use App\Models\Doctor\ConsultationDiagnosisJoin;
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
            'consultation_type' => 'nullable|exists:consultation_types,name',
            'diagnosis' => 'nullable|array',
            'clinical_history' => 'nullable',
            'chief_complains' => 'required|array',
            'physical_examinations' => 'nullable|array',
        ]);

        count(Consultation::selectConsultations(null, $request->visit_id, null)) > 0 ? throw new InputsValidationException("Consultation already exists!!! kindly update instead...") : null;

        $request->consultation_type ? $consultation_type_id = ConsultationType::where('name', $request->consultation_type)->get('id')[0]['id'] : $consultation_type_id = null;

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

                $existing_chief_complain_in_join = ConsultationSymptomsJoin::where('symptom_id', $existing_chief_complain[0]['id'])->get('id');
                count($existing_chief_complain_in_join) < 1 ?
                    ConsultationSymptomsJoin::create([
                        'consultation_id' => $created->id,
                        'symptom_id' => $existing_chief_complain[0]['id'],
                    ]) 
                    :
                null;
                // ConsultationSymptomsJoin::create([
                //     'consultation_id' => $created->id,
                //     'symptom_id' => $existing_chief_complain[0]['id'],
                // ]);

                
            }
            // create chief physical examinations
            if($request->physical_examinations != null){
                foreach($request->physical_examinations as $physical_examination){
                    foreach($physical_examination as $key => $examination){
    
                        $existing_physical_examination = PhysicalExaminationType::where('name', $key)->get('id');
    
                        count($existing_physical_examination) < 1 ? throw new InputsValidationException("Physical examination with name: " . $key . " does not exist!!!") : null;
    
                        $existing_physical_examination_in_join = ConsultationPhysicalExaminationsJoin::where('physical_examination_id', $existing_physical_examination[0]['id'])->get('id');
                        count($existing_physical_examination_in_join) < 1 ?
                            ConsultationPhysicalExaminationsJoin::create([
                                'consultation_id' => $created->id,
                                'physical_examination_id' => $existing_physical_examination[0]['id'],
                                'findings' => $examination
                            ]) 
                            :
                        null;
                        // ConsultationPhysicalExaminationsJoin::create([
                        //     'consultation_id' => $created->id,
                        //     'physical_examination_id' => $existing_physical_examination[0]['id'],
                        //     'findings' => $examination
                        // ]);
    
                    }               
                    
                    
                }
            }
            
            if($request->diagnosis != null){
                foreach($request->diagnosis as $diagnosis){
                    $existing_diagnosis = Diagnosis::where('name', $diagnosis)->get('id');

                    count($existing_diagnosis) < 1 ? throw new InputsValidationException("Diagnosis with name: " . $diagnosis . " does not exist!!!") : null;

                    count(ConsultationDiagnosisJoin::where('diagnosis_id', $existing_diagnosis[0]['id'])->get('id')) < 1 ?
                        ConsultationDiagnosisJoin::create([
                            'consultation_id' => $created->id,
                            'diagnosis_id' => $existing_diagnosis[0]['id']
                        ]) 
                        :
                    null;
                    
                }
                // $existing_diagnosis = Diagnosis::where('name', $request->diagnosis)->get('id');
                // ConsultationDiagnosisJoin::create([
                //     'consultation_id' => $created->id,
                //     'diagnosis_id' => $existing_diagnosis[0]['id']
                // ]);
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

    //create consultation
    public function updateConsultation(Request $request){
        $request->validate([
            'id' => 'required|exists:consultations,id',
            'visit_id' => 'required|exists:visits,id',
            'consultation_type' => 'nullable|exists:consultation_types,name',
            'diagnosis' => 'nullable|array',
            'clinical_history' => 'nullable',
            'chief_complains' => 'required|array',
            'physical_examinations' => 'nullable|array',
        ]);

        $request->consultation_type ? $consultation_type_id = ConsultationType::where('name', $request->consultation_type)->get('id')[0]['id'] : $consultation_type_id = null;

        try{

            DB::beginTransaction();

            $created = Consultation::where('id', $request->id)
            ->update([
                'visit_id'=>$request->visit_id,
                'consultation_type_id'=>$consultation_type_id,
                'clinical_history'=>$request->clinical_history,
                'updated_by'=> User::getLoggedInUserId()
            ]);


            // create chief complains
            foreach($request->chief_complains as $chief_complain){
                
                $existing_chief_complain = Symptom::where('name', $chief_complain)->get('id');

                count($existing_chief_complain) < 1 ? throw new InputsValidationException("Chief complain with name: " . $chief_complain . " does not exist!!!") : null;

                $existing_chief_complain_in_join = ConsultationSymptomsJoin::where('symptom_id', $existing_chief_complain[0]['id'])->get('id');
                count($existing_chief_complain_in_join) < 1 ?
                    ConsultationSymptomsJoin::create([
                        'consultation_id' => $created->id,
                        'symptom_id' => $existing_chief_complain[0]['id'],
                    ]) 
                    :
                    null;

                
            }
            // create chief physical examinations
            if($request->physical_examinations != null){
                foreach($request->physical_examinations as $physical_examination){
                    foreach($physical_examination as $key => $examination){
    
                        $existing_physical_examination = PhysicalExaminationType::where('name', $key)->get('id');
    
                        count($existing_physical_examination) < 1 ? throw new InputsValidationException("Physical examination with name: " . $key . " does not exist!!!") : null;
    
                        $existing_physical_examination_in_join = ConsultationPhysicalExaminationsJoin::where('physical_examination_id', $existing_physical_examination[0]['id'])->get('id');
                        count($existing_physical_examination_in_join) < 1 ?
                            ConsultationPhysicalExaminationsJoin::create([
                                'consultation_id' => $created->id,
                                'physical_examination_id' => $existing_physical_examination[0]['id'],
                                'findings' => $examination
                            ]) 
                            :
                        null;


                        // ConsultationPhysicalExaminationsJoin::create([
                        //     'consultation_id' => $created->id,
                        //     'physical_examination_id' => $existing_physical_examination[0]['id'],
                        //     'findings' => $examination
                        // ]);
    
                    }               
                    
                    
                }
            }
            
            if($request->diagnosis != null){
                foreach($request->diagnosis as $diagnosis){
                    $existing_diagnosis = Diagnosis::where('name', $diagnosis)->get('id');

                    count($existing_diagnosis) < 1 ? throw new InputsValidationException("Diagnosis with name: " . $diagnosis . " does not exist!!!") : null;

                    $existing_diagnosis_in_join = ConsultationDiagnosisJoin::where('diagnosis_id', $existing_diagnosis[0]['id'])->get('id');
                    count($existing_diagnosis_in_join) < 1 ?
                        ConsultationDiagnosisJoin::create([
                            'consultation_id' => $created->id,
                            'diagnosis_id' => $existing_diagnosis[0]['id']
                        ]) 
                        :
                    null;
                }
                $existing_diagnosis = Diagnosis::where('name', $request->diagnosis)->get('id');

                $existing_diagnosis_in_join = ConsultationDiagnosisJoin::where('diagnosis_id', $existing_diagnosis[0]['id'])->get('id');
                count($existing_diagnosis_in_join) < 1 ?
                    ConsultationDiagnosisJoin::create([
                        'consultation_id' => $created->id,
                        'diagnosis_id' => $existing_diagnosis[0]['id']
                    ]) 
                    :
                null;
                // ConsultationDiagnosisJoin::create([
                //     'consultation_id' => $created->id,
                //     'diagnosis_id' => $existing_diagnosis[0]['id']
                // ]);
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

    public function getConsultation(Request $request){
        $consultation = Consultation::selectConsultations($request->id, $request->visit_id, null);

        count($consultation) < 1 ? throw new InputsValidationException("Consultation not found!!!") : null;

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched a consultation with details id: ". $request->id. " visit_id: ". $request->visit_id);

        return response()->json(
            $consultation
        ,200);
    }
    public function getAllConsultations(){
        $consultation = Consultation::selectConsultations(null, null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all consultations");

        return response()->json(
            $consultation
        ,200);
    }
    public function softDeleteConsultation($id){
        $existing = Consultation::selectConsultations($id, null, null);

        if(count($existing) < 1){
            throw new InputsValidationException("Consultation with id: ". $id . " not found!!!");
        }
        
        Consultation::where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => User::getLoggedInUserId(),
                ]);
        UserActivityLog::createUserActivityLog(APIConstants::NAME_SOFT_DELETE, "Trashed a consultation with id: ". $id);
        return response()->json(
            []
        ,200);  
    }

    public function restoreConsultation($id){
        $existing = Consultation::where('id', $id)->get();

        if(count($existing) < 1){
            throw new InputsValidationException("Consultation with id: ". $id . " not found!!!");
        }
        
        Consultation::where('id', $id)
                ->update([
                    'deleted_at' => null,
                    'deleted_by' => null
                ]);
        UserActivityLog::createUserActivityLog(APIConstants::NAME_RESTORE, "Restore a consultation with id: ". $id);
        return response()->json(
            []
        ,200);  
    }
    public function permanentlyDelete($id){
        $existing = Consultation::where('id', $id)->get();

        if(count($existing) < 1){
            throw new InputsValidationException("Consultation with id: ". $id . " not found!!!");
        }
        
        Consultation::destroy($id);
        UserActivityLog::createUserActivityLog(APIConstants::NAME_PERMANENT_DELETE, "Permanently deleted a consultation with id: ". $id);
        return response()->json(
            []
        ,200);
    }
}
