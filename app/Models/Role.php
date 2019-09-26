<?php

namespace App\Models;

use Zizaco\Entrust\EntrustRole;
use Zizaco\Entrust\Traits\EntrustRoleTrait;

class Role extends EntrustRole
{
    use EntrustRoleTrait;

    public function permissionUser()
    {
        return $this->belongsToMany('App\Models\User', 'role_user', 'role_id', 'user_id');
    }
}
