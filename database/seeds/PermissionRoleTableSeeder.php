<?php

use Illuminate\Database\Seeder;
use App\Services\RoleUserService;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionRole;

class PermissionRoleTableSeeder extends Seeder
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

    public function getAdminPermssion()
    {

        return DB::table ( "permissions" )->select ( "name" )->get ();
    }

    public function getMangerPermssion()
    {

        return DB::table ( "permissions" )->select ( "name" )->whereIN ( "name", ['DASHBOARD', 'REPORTS_CHARTS',
            'INVESTING', 'RESEARCHING', 'OPERATION', 'REFERENCE_LIBRARY', 'HEMP_COMINGSOON', 'FAQ_HELP',
            'GLOBAL_COMINGSOON',
            'CONTACTUS', 'ABOUTUS', 'ADMINTOOLS'] )->get ();
    }

    public function getEquioPermssion()
    {

        return DB::table ( "permissions" )->select ( "name" )->whereIN ( "name", ['DASHBOARD', 'REPORTS_CHARTS',
            'INVESTING', 'RESEARCHING', 'OPERATION', 'REFERENCE_LIBRARY', 'HEMP_COMINGSOON', 'FAQ_HELP',
            'GLOBAL_COMINGSOON',
            'CONTACTUS', 'ABOUTUS'] )->get ();
    }


    public function getReporterPermssion()
    {

        return DB::table ( "permissions" )->select ( "name" )->whereIN ( "name", ['DASHBOARD', 'REPORTS_CHARTS',
            'INVESTING', 'RESEARCHING', 'OPERATION', 'REFERENCE_LIBRARY', 'HEMP_COMINGSOON', 'FAQ_HELP',
            'GLOBAL_COMINGSOON',
            'CONTACTUS', 'ABOUTUS'] )->get ();
    }

    public function getEditorPermssion()
    {

        return DB::table ( "permissions" )->select ( "name" )->whereIN ( "name", ['DASHBOARD', 'REPORTS_CHARTS',
            'INVESTING', 'RESEARCHING', 'OPERATION', 'REFERENCE_LIBRARY', 'HEMP_COMINGSOON', 'FAQ_HELP',
            'GLOBAL_COMINGSOON',
            'CONTACTUS', 'ABOUTUS'] )->get ();
    }


    public function run()
    {

        DB::table("permission_role")->delete();
        $this->assignAdminPermission ();
        $this->assignMangerPermission ();
        $this->assignEquioPermission ();
        $this->assignEditorPermission ();
        $this->assignReporterPermission ();


    }

    public function assignAdminPermission()
    {

        foreach ( $this->getAdminPermssion () as $namePermission ) {

            $permission = $this->role_user->getPermissionByName ( $namePermission->name );
            $role = $this->role_user->getRoleByName ( "ADMINISTRATOR" );
            if ( $this->role_user->getRolePermissionExists ( $role->id, $permission->id ) ) {

            } else {

                $role->attachPermission ( $permission );
            }

        }

    }


    public function assignMangerPermission()
    {

        foreach ( $this->getMangerPermssion () as $namePermission ) {

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

        foreach ( $this->getEquioPermssion () as $namePermission ) {

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

        foreach ( $this->getReporterPermssion () as $namePermission ) {

            $permission = $this->role_user->getPermissionByName ( $namePermission->name );
            $role = $this->role_user->getRoleByName ( "REPORTER" );
            if ( $this->role_user->getRolePermissionExists ( $role->id, $permission->id ) ) {

            } else {

                $role->attachPermission ( $permission );
            }

        }

    }


    public function assignEditorPermission()
    {

        foreach ( $this->getEditorPermssion () as $namePermission ) {

            $permission = $this->role_user->getPermissionByName ( $namePermission->name );
            $role = $this->role_user->getRoleByName ( "EDITOR" );
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
