<?php

namespace App\Models;

use Zizaco\Entrust\EntrustPermission;
use Zizaco\Entrust\Traits\EntrustPermissionTrait;

class Permission extends EntrustPermission
{
    use EntrustPermissionTrait;
}
