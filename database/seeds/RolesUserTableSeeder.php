<?php

use Illuminate\Database\Seeder;
use App\Services\RoleUserService;
use Illuminate\Support\Facades\DB;
use App\Models\RoleUser;
use App\Models\User;

class RolesUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private $role_user;

    public function __construct(RoleUserService $roleUserService)
    {
        $this->role_user = $roleUserService;
    }


    public function getAllUsers()
    {

        return DB::table ( "users" )->select ( "id", "email", "role" )->get ();
    }


    public function assignRoleUser($userId, $role)
    {

        $user = User::find ( $userId );
        $role = $this->role_user->getRoleByName ( $role );
        $user->attachRole ( $role );
    }


    public function run()
    {

        DB::table ( "role_user" )->delete ();
        $allUsers = $this->getAllUsers ();

        foreach ( $allUsers as $users ) {


            switch ($users->role) {

                case 1:
                    //admin
                    $this->assignRoleUser ( $users->id, 'ADMINISTRATOR' );

                    break;
                case 2:
                    //equio
                    $this->assignRoleUser ( $users->id, 'EQUIO' );

                    break;
                case 3:
                    //manger
                    $this->assignRoleUser ( $users->id, 'MANAGER' );
                    break;
                case 4:
                    //editor
                    $this->assignRoleUser ( $users->id, 'EDITOR' );
                    break;
                case 5:
                    //reporter
                    $this->assignRoleUser ( $users->id, 'REPORTER' );
                    break;
                default:
                    break;

            }


        }


    }
}
