<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Admin\Clinic;
use App\Models\Admin\PaymentType;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //call the define roles and permissions function to run  on start up
        $this->defineRolesAndPermissions();

        // $this->createDefaultClinics();

        // $this->createDefaultPaymentTypes();
    }

    private function defineRolesAndPermissions()
    {
        // Define roles
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'doctor']);
        Role::firstOrCreate(['name' => 'nurse']);
        Role::firstOrCreate(['name' => 'front_office']);
        Role::firstOrCreate(['name' => 'laboratory']);
        Role::firstOrCreate(['name' => 'pharmacy']);
        Role::firstOrCreate(['name' => 'imaging']);
        Role::firstOrCreate(['name' => 'theatre']);
        Role::firstOrCreate(['name' => 'dental']);
        Role::firstOrCreate(['name' => 'catering']);
        Role::firstOrCreate(['name' => 'ambulance']);
        Role::firstOrCreate(['name' => 'dialysis']);
        Role::firstOrCreate(['name' => 'ophalmology']);
        Role::firstOrCreate(['name' => 'special_clinic']);
        Role::firstOrCreate(['name' => 'procurement']);
        Role::firstOrCreate(['name' => 'human_resource']);
        Role::firstOrCreate(['name' => 'inventory']);
        Role::firstOrCreate(['name' => 'billing']);
        Role::firstOrCreate(['name' => 'accounting']);

        // Define permissions
        // Permission::firstOrCreate(['name' => 'create_post']);
        // Permission::firstOrCreate(['name' => 'edit_post']);
        // Permission::firstOrCreate(['name' => 'delete_post']);

        if(count(User::where('name','admin')->orWhere('email', 'maimoon@maimoon.com')->get()) < 1){
            $Branch = Branch::firstOrCreate([
                'name'=>'default',
                'active'=>1,
                ]);
            $user=User::firstOrCreate([
                'name'=>'admin',
                'email'=> 'maimoon@maimoon.com',
                'branch_id'=>$Branch->id,
                'active'=>1,
                'deleted_by'=>null,
                'deleted_at'=>null,
                'password'=>bcrypt('secret'),
                ]);
            
            $role = Role::findByName('admin');
            if($role){
                $user->assignRole('admin');
            }
    
            // $all_permissions = Permission::all();
            // foreach($all_permissions as $permission){
            //     $user->givePermissionTo($permission->name);
            // }
        }
        
    }

    private function createDefaultPaymentTypes(){
        PaymentType::firstOrCreate([
            "name" => "Cash",
            "description" => "Used when patient has cash at hand"
        ]);

        PaymentType::firstOrCreate([
            "name" => "Bank",
            "description" => "Used when patient offers to pay via bank"
        ]);

        PaymentType::firstOrCreate([
            "name" => "Mpesa",
            "description" => "Used when patient offers to pay via mpesa"
        ]);

        PaymentType::firstOrCreate([
            "name" => "Insurance",
            "description" => "Used when patient has and wishes to use their health insurance cover"
        ]);

        PaymentType::firstOrCreate([
            "name" => "Other",
            "description" => "Any other payment methods"
        ]);
    }

    private function createDefaultClinics(){
        Clinic::firstOrCreate([
            "name" => "ENT",
            "description" => "Ear, Nose, and Throat"
        ]);

        Clinic::firstOrCreate([
            "name" => "Dentist",
            "description" => "Dentist Clinic"
        ]);

        Clinic::firstOrCreate([
            "name" => "Other",
            "description" => "Any other not yet listed"
        ]);
    }
}
