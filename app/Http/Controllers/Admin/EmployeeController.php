<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AlreadyExistsException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Models\Admin\Department;
use App\Models\Admin\EmployeeDepartmentJoin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{

    public function createEmployee(Request $request){
        $request->validate([
            'employee_name' => 'required|string|min:2|max:255',
            'ipnumber'=>'required|unique:employees,ipnumber',
            'age' => 'integer|min:0|max:200',
            'dob' => 'required|date|before:today',
            'user_id' => 'required|integer|min:1|exists:users,id',
            'speciality' => 'required|string|min:3|max:25',
            'role' => 'required|string|min:2|max:255|exists:roles,name',
            'departments' => 'required'
        ]);
            

        $employeeCode = $this->generateEmployeeCode();

        DB::beginTransaction();
        try {
            $created_employee = Employee::create([
                'employee_name' => $request->employee_name, 
                'ipnumber'=>$request->ipnumber,
                'employee_code'=>$employeeCode, 
                'age' => $request->age,
                'dob' => $request->dob,
                'role'=>$request->role,
                'speciality' => $request->speciality,
                'user_id' => $request->user_id,
                'created_by' => Auth::user()->id
            ]);

            Auth::user()->assignRole($request->role);

            foreach ($request->departments as $department) {

                //use this if department will be provide as an array of json objects
                // $validator = Validator::make((array) $department, [            
                //     'name' => 'required|exists:department,name'
                // ]);

                // if ($validator->fails()) {
                //     return response()->json(['errors' => $validator->errors()], 422);
                // }

                //get department name and save it to db
                $existing_department_details = Department::selectDepartments(null, $department);

                count($existing_department_details) < 1 ? throw new InputsValidationException("No Department with named: ". $department." exists!") : null;

                //if user is already assigned to department continue... no need to save
                if(count(EmployeeDepartmentJoin::where('employee_id', $created_employee->id)->where('department_id', $existing_department_details[0]['id'])) > 0){
                    continue;
                }

                EmployeeDepartmentJoin::create([
                    'employee_id' => $created_employee->id,
                    'department_id' => $existing_department_details[0]['id'],
                ]);
            }

            DB::commit();
        }
        catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created an employee with name: ". $request->employee_name);

        return response()->json(
            Employee::selectEmployees(null, $request->ipnumber, null)
        ,200);

    }

    //function to generate employeecode
    private function generateEmployeeCode(){
        // Generate a random six-digit number
        $randomNumber = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        // Add the EMP prefix
        $employeeCode = 'EMP' . $randomNumber;

        // Check if the code already exists in the database
        while (Employee::where('employee_code', $employeeCode)->exists()) {
            $randomNumber = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $employeeCode = 'EMP' . $randomNumber;
        }

        return $employeeCode;
    }


//updating employer details
    public function updateEmployee(Request $request){
        $request->validate([
            'id' => 'required|integer|min:1|exists:employees,id',
            'ipnumber'=> 'required|integer|min:1|exists:employees,ipnumber',
            'age' => 'integer|min:0|max:200',
            'dob'=> 'required|date:before:today',
            'role'=> 'required|string|min:2|max:255|exists:roles,name',
            'employee_name'=> 'string|min:1|max:255',
            'speciality'=> 'required|string|min:1|max:255'
        ]);

        $existing = Employee::selectEmployees(null, $request->ipnumber, null);

        if(count($existing) > 0 && $existing[0]["id"] != $request->id){
            throw new AlreadyExistsException(APIConstants::NAME_EMPLOYEE. " ". $request->id);
        }

        Employee::where('id', $request->id)
                ->update([
                    'employee_name'=>$request->employee_name, 
                    'age' =>$request->age,
                    'dob'=>$request->dob,
                    'role'=> $request->role,
                    'speciality'=> $request->speciality,
                    'updated_by' => User::getLoggedInUserId()
                ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_UPDATE, "Updated an employee with id: ". $request->id);
        

        return response()->json(
            Employee::selectEmployees($request->id, null, null)
        ,200);

    }

    //getting single employee
    public function getSingleEmployee(Request $request){

        if($request->id == null && $request->ipnumber == null && $request->employee_code == null){
            throw new InputsValidationException("id or employee code or ipnumber required!");
        }

        $employee = Employee::selectEmployees($request->id, $request->ipnumber, $request->employee_code);

        if(count($employee) < 1){
            throw new NotFoundException(APIConstants::NAME_EMPLOYEE);
        }

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched an employee with id: ". $employee[0]['id']);

        return response()->json(
            $employee
        ,200);
    }

    

    public function getAllEmployees(){

        $employees = Employee::selectEmployees(null, null, null);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_GET, "Fetched all employees");


        return response()->json(
            $employees
        ,200);
    }


    public function approveEmployee($id){
            
        $existing = Employee::selectEmployees($id, null, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_EMPLOYEE. " with id: ". $id);
        }

        Employee::where('id', $id)
                ->update([
                    'approved_by' => User::getLoggedInUserId(),  
                    'approved_at' => Carbon::now(),
                    'disabled_by' => null,
                    'disabled_at' => null,
                ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_APPROVE, "Approved an Employee with id: ". $id);

        return response()->json(
            Employee::selectEmployees($id, null, null)
        ,200);
    }

    public function disableEmployee($id){
            
        $existing = Employee::selectEmployees($id, null, null);

        if(count($existing) < 1){
            throw new NotFoundException(APIConstants::NAME_EMPLOYEE. " with id: ". $id);
        }

        Employee::where('id', $id)
                ->update([
                    'approved_by' => null, 
                    'approved_at' => null,
                    'disabled_by' => User::getLoggedInUserId(),
                    'disabled_at' => Carbon::now(),
                ]);

        UserActivityLog::createUserActivityLog(APIConstants::NAME_DISABLE, "Disabled an employee with id: ". $id);

        return response()->json(
            Employee::selectEmployees($id, null, null)
        ,200);
    }


}
