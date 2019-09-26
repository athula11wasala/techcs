<?php


namespace App\Services;

use App\Repositories\PermissionRepository;
use App\Repositories\PermissionRoleRepository;
use App\Repositories\RoleRepository;
use App\Repositories\RoleUserRepository;
use Illuminate\Http\Request;

class RoleUserService
{
    private $role;
    private $role_user;
    private $permission;
    private $permission_role;

    public function __construct(RoleRepository $roleRepository, RoleUserRepository $roleUserRepository,
                                PermissionRepository $permissionRepository,
                                PermissionRoleRepository $permissionRoleRepository)
    {
        $this->role=$roleRepository;
        $this->role_user=$roleUserRepository;
        $this->permission=$permissionRepository;
        $this->permission_role=$permissionRoleRepository;
    }

    public function getUserRolesAndPermissions($user_id) {

        return $this->role_user->userRolesAndPermissions($user_id);
    }

    public function getRoleByName($role_name){
        return $this->role->roleByName($role_name);
    }

    public function getAddNewPermission(Request $request){
        return $this->permission->addNewPermission($request);
    }

    public function getPermissionByName($name){
        return $this->permission->permissionByName($name);
    }

    public function getAddRolePermission($role_id, $permission_id){
        return $this->permission_role->addRolePermission($role_id, $permission_id);
    }

    public function getRolePermissionExists($role_id, $permission_id){
        return $this->permission_role->rolePermissionExists($role_id, $permission_id);
    }

    public function getRoleNameAndId(){
        return $this->role->allRoleDetail();
    }

}