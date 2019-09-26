<?php

namespace App\Repositories;
use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Http\Request;

class PermissionRepository extends Repository
{
    public function model()
    {
        return 'App\Models\Permission';
    }

    public function addNewPermission(Request $request){
        $permission=new $this->model;
        $permission->name         = $request->name;
        $permission->display_name = $request->permission_display_name;
        $permission->description  = $request->permission_description;
        $permission->save();

        return $permission;
    }

    public function permissionByName($name){
        $permission=$this->model->where('name', $name)
            ->first();
        return $permission;
    }


}
