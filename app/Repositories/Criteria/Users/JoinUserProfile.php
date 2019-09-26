<?php


namespace App\Repositories\Criteria\Users;

use Bosnadev\Repositories\Criteria\Criteria;
use Bosnadev\Repositories\Contracts\RepositoryInterface as Repository;
use Bosnadev\Repositories\Criteria\RepositoryInterface;

class JoinUserProfile extends Criteria
{

    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        $model = $model->join('user_profiles', 'user_profiles.user_id', '=', 'users.id');
        return $model;
    }
}