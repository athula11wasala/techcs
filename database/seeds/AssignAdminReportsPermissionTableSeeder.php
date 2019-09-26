<?php

use Illuminate\Database\Seeder;
use App\Services\RoleUserService;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionRole;

class AssignAdminReportsPermissionTableSeeder extends Seeder
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

    public function getPermssion()
    {

        return DB::table ( "permissions" )->select ( "name" )->whereIN ( "name",
            ['ADMINREPORTS'] )->get ();
    }


    public function run()
    {
        $this->assignReporterPermission ();
    }


    public function assignReporterPermission()
    {

        foreach ( $this->getPermssion () as $namePermission ) {

            $permission = $this->role_user->getPermissionByName ( $namePermission->name );
            $role = $this->role_user->getRoleByName ( "REPORTER" );
            if ( $this->role_user->getRolePermissionExists ( $role->id, $permission->id ) ) {

            } else {

                $role->attachPermission ( $permission );
            }

        }

    }
}
