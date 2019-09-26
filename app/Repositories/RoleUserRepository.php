<?php

namespace App\Repositories;
use Bosnadev\Repositories\Eloquent\Repository;

class RoleUserRepository extends Repository
{
    public function model()
    {
        return 'App\Models\RoleUser';
    }

    public function userRolesAndPermissions($user_id){
        $all=$this->model->where('user_id', $user_id)
            ->with(['role','permissionRole.permission'])
                ->with(array('role'=>function($query){
                $query->select('id','name', 'display_name','description'
                );
            },
            'permissionRole.permission'=>function($query){
            $query->select('id','name', 'display_name','description'
            );
        }
            ))
        ->get();
        return $all;
    }
}
