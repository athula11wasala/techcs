<?php

use Illuminate\Database\Seeder;
use App\Services\RoleUserService;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionRole;

class AssignRequetAnaylistPermissionTableSeeder extends Seeder
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
                                                    ['REQUEST_TIME_WITH_ANINDUSTRY_ANALYST'] )->get ();
    }


    public function run()
    {

        $this->assignEditorPermission ();
        $this->assignMangerPermission ();
        $this->assignEquioPermission ();
        $this->assignReporterPermission ();

    }


    public function assignEditorPermission()
    {

        foreach ( $this->getPermssion () as $namePermission ) {

            $permission = $this->role_user->getPermissionByName ( $namePermission->name );
            $role = $this->role_user->getRoleByName ( "EDITOR" );
            if ( $this->role_user->getRolePermissionExists ( $role->id, $permission->id ) ) {

            } else {

                $role->attachPermission ( $permission );
            }

        }

    }

    public function assignMangerPermission()
    {

        foreach ( $this->getPermssion () as $namePermission ) {

            $permission = $this->role_user->getPermissionByName ( $namePermission->name );
            $role = $this->role_user->getRoleByName ( "MANAGER" );
            if ( $this->role_user->getRolePermissionExists ( $role->id, $permission->id ) ) {

            } else {

                $role->attachPermission ( $permission );
            }

        }

    }

    public function assignEquioPermission()
    {

        foreach ( $this->getPermssion () as $namePermission ) {

            $permission = $this->role_user->getPermissionByName ( $namePermission->name );
            $role = $this->role_user->getRoleByName ( "EQUIO" );
            if ( $this->role_user->getRolePermissionExists ( $role->id, $permission->id ) ) {

            } else {

                $role->attachPermission ( $permission );
            }

        }

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

    public function rolePermissionExists($role_id, $permission_id)
    {
        $permission_role = PermissionRole::where ( 'permission_id', $permission_id )
            ->where ( 'role_id', $role_id )
            ->first ();
        if ( $permission_role ) {
            return true;
        } else {
            return false;
        }
    }
}
