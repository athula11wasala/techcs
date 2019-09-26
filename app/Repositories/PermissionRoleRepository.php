<?php

namespace App\Repositories;
use Bosnadev\Repositories\Eloquent\Repository;

class PermissionRoleRepository extends Repository
{
    public function model()
    {
        return 'App\Models\PermissionRole';
    }

    public function addRolePermission($role_id, $permission_id){
        return $this->model->create(['permission_id' => $permission_id, 'role_id' => $role_id]);

        $permission=new $this->model;
        $permission->role_id=$role_id;
        $permission->permission_id=$permission_id;

        $permission->save();
        return $permission;
    }

    public function rolePermissionExists($role_id, $permission_id){
        $permission_role=$this->model->where('permission_id', $permission_id)
            ->where('role_id', $role_id)
            ->first();
        if ($permission_role){
            return true;
        } else{
            return false;
        }
    }

}
