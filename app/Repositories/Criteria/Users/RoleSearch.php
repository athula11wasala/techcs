<?php
/**
 * Created by PhpStorm.
 * User: thilan
 * Date: 6/6/18
 * Time: 4:01 PM
 */

namespace App\Repositories\Criteria\Users;

use Bosnadev\Repositories\Contracts\RepositoryInterface as Repository;
use Bosnadev\Repositories\Criteria\Criteria;
use Bosnadev\Repositories\Criteria\RepositoryInterface;

class RoleSearch extends Criteria
{

    /**
     * RoleSearch constructor.
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }


    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        $model = $model
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.id', '=', $this->value);
        return $model;
    }
}