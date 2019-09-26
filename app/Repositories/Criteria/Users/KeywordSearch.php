<?php


namespace App\Repositories\Criteria\Users;

use Bosnadev\Repositories\Criteria\Criteria;
use Bosnadev\Repositories\Contracts\RepositoryInterface as Repository;
use Bosnadev\Repositories\Criteria\RepositoryInterface;

class KeywordSearch extends Criteria
{
    /**
     * @var
     */
    private $value;

    /**
     * FieldLike constructor.
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
        $value = $this->value;
        $model = $model->where(function ($subQuery) use ($value) {
            $subQuery->where('email', 'like', '%' . $value . '%')
                ->orWhere('first_name', 'like', '%' . $value . '%')
                ->orWhere('last_name', 'like', '%' . $value . '%');
        });
        return $model;
    }
}