<?php

namespace App\Traits;

use App\Rules\validateExcel;
use Illuminate\Support\Facades\Validator;

trait RoleUserValidators
{
    protected function validatePermissionsAdd(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|unique:permissions',
            'permission_display_name' => 'required',
            'permission_description' => 'required',
        ]);
    }

    protected function validatePermissionsAddToRole(array $data)
    {
        return Validator::make($data, [
            'permission_name' => 'required|exists:permissions,name',
            'role' => 'required|exists:roles,name',
        ]);
    }

}