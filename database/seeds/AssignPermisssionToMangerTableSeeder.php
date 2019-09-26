<?php

use Illuminate\Database\Seeder;
use App\Services\RoleUserService;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionRole;

class AssignPermisssionToMangerTableSeeder extends Seeder
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


    public function getEditorPermssion()
    {

        return DB::table ( "permissions" )->select ( "name" )->whereIN ( "name", ['CONTACTMANAGEMENT'] )->get ();
    }


    public function run()
    {

        $this->assignEditorPermission ();

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
