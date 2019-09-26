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

class AttributeSearch extends Criteria
{

    /**
     * AttributeSearch constructor.
     * @param $attribute
     * @param $value
     * @param null $role
     */
    public function __construct($attribute, $value)
    {
        $this->value = $value;
        $this->attribute = $attribute;
    }


    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        $model = $model->where($this->attribute, $this->value);
        return $model;
    }
}