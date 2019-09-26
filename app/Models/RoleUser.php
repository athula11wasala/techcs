<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $table = 'role_user';
    protected $fillable = [
        'user_id',
        'role_id',
    ];

    public function role()
    {
        return $this->hasOne('App\Models\Role', 'id', 'role_id');
    }

    public function permissionRole()
    {
        return $this->hasMany('App\Models\PermissionRole', 'role_id', 'role_id');
    }
}
