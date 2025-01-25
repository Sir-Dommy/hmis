<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserActivityLog;
use App\Utils\APIConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function test(){
        $users = User::all();
        return response()->json($users);
    }
    //registration function
    public function register(Request $request){
        try{
            $request->validate([
                'name' => 'required|string|min:3|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'branch_id' => 'required|integer|min:1|exists:branches,id',
                'active' => 'required|boolean|',
                'password' => 'required|string|min:6|max:64',
                'role' => 'required|string|min:2|max:255|exists:roles,name'
                
            ]);
            DB::beginTransaction();
            $branches = Branch::where('id', $request->branch_id)->get();
            if($branches[0]->active == 0 || $branches[0]->deleted_by != null || $branches[0]->deleted_at != null ){
                return response()->json([
                    'message' => 'forbidden action, your branch is inactive, or deleted',
                ], 403);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'branch_id'=>$request->branch_id,
                'active'=>$request->active,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);
    
            DB::commit();
            
            UserActivityLog::createUserActivityLog(APIConstants::NAME_CREATE, "Created a user with email: ". $user->email);

            return response()->json([
                'name' => $user->name,
                'email' => $user->email,
                'branch_id'=>$user->branch_id,
                'active'=>$user->active,
            ],200);
    
            // $token = Auth::login($user);
            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'User created successfully',
            //     'user' => $user,
            //     'authorisation' => [
            //         'token' => $token,
            //         'type' => 'bearer',
            //     ]
            // ]);
        }
        catch(ValidationException $e){
            return response()->json(['validation_error'=>$e->getMessage()], 422);
        }

        catch(\Exception $e){
            DB::rollBack();
            return response()->json(['error'=>$e->getMessage()], 500);
        }

        
    }


    //login function
    public function login(Request $request){

        try{
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
            $all = User::where('email', $request->email)->get();
            if(count($all) > 0){
                if($all[0]->active == 0 || $all[0]->deleted_by != null || $all[0]->deleted_at != null ){
                    return response()->json([
                        'message' => 'forbidden action',
                    ], 403);
                }
                $branches = Branch::where('id', $all[0]->branch_id)->get();
                if(count($branches) < 1 || $branches[0]->active == 0 || $branches[0]->deleted_by != null || $branches[0]->deleted_at != null ){
                    return response()->json([
                        'message' => 'forbidden action, your branch is inactive',
                    ], 403);
                }
            }
            
            $credentials = $request->only('email', 'password');
    
            $token = Auth::attempt($credentials);
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }
    
            $user = Auth::user();
            
            UserActivityLog::createUserActivityLog("LOGIN", "User logged in........ Email : ". $user->email);

            return response()->json([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'branch_id'=>$user->branch_id,
                    'active'=>$user->active,
                    'authorisation' => [
                        'token' => $token,
                        'type' => 'bearer',
                    ],
                    'message' => 'success',
                ]);
        }
        catch(ValidationException $e){
            return response()->json(['validation_error'=>$e->getMessage()], 422);
        }

        catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()], 500);
        }
        

    }

    // logout function
    public function logout(){
        $user =  Auth::user();
        Auth::logout();
            
        UserActivityLog::createUserActivityLog("LOGGET OUT", "User logged out........ Email : ". $user->email);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    // function to refresh jwt token
    public function refresh(){
        $user = Auth::user();
        
        UserActivityLog::createUserActivityLog("REFRESHING_TOKEN", "User refreshed token........ Email : ". $user->email);

        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

}
