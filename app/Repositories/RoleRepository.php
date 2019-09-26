<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;

class RoleRepository extends Repository
{
    public function model()
    {
        return 'App\Models\Role';
    }

    public function roleByName($role_name)
    {
        $role = $this->model->where ( 'name', $role_name )
            ->first ();
        return $role;
    }


    public function allRoleDetail()
    {
        $data = [];
        $role = $this->model->select ( "id", "name" )
            ->get ();
        foreach ( $role as $rows ) {

            $data[] = ['id' => $rows->id, 'name' => ucfirst ( strtolower ( $rows->name ) )];

        }
        return $data;
    }

}

