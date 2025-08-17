<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\PGDBManagerService;
use App\Models\User;
/**
 * Sync postgres server/cluster roles and databases with UOD PGDB_Products and PGDB_Roles
 */
class SyncPGwithUOD implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $service = new PGDBManagerService();
        
        $databases = $service->getAllDatabases();
        $roles = $service->getUsers();

        if ($databases !== null) {
            foreach ($databases as $database) {
                $roleName = $role->rolname;
                $rolePassword = $role->rolpassword;
                $roleIsSuperuser = $role->rolsuper;
                $roleIsLogin = $role->rolcanlogin;

                if($roleIsLogin && $roleIsSuperuser){
                    $user_id = explode('_', $roleName)[0];
                    $user = User::where('id', $user_id)->first();
                    
                    if($user){
                        DB::table("pgdb_roles")->insert([
                            "user_id" => $user_id,
                            "role_name" => $roleName,
                            "role_password" => $rolePassword,
                            "role_is_superuser" => $roleIsSuperuser,
                            "role_is_login" => $roleIsLogin,
                        ]);
                        
                    }
                }
            }

    //     if ($roles !== null) {
    //         foreach ($roles as $role) {
    //             $roleName = $role->rolname;
    //             $rolePassword = $role->rolpassword;
    //             $roleIsSuperuser = $role->rolsuper;
    //             $roleIsLogin = $role->rolcanlogin;

    //             if($roleIsLogin && $roleIsSuperuser){
    //                 $user_id = explode('_', $roleName)[0];
    //                 $user = User::where('id', $user_id)->first();
                    
    //                 if($user){
    //                     DB::table("pgdb_roles")->insert([
    //                         "user_id" => $user_id,
    //                         "role_name" => $roleName,
    //                         "role_password" => $rolePassword,
    //                         "role_is_superuser" => $roleIsSuperuser,
    //                         "role_is_login" => $roleIsLogin,
    //                     ]);
                        
    //                 }
    //             }
    //         }
    //     }
    }
}
