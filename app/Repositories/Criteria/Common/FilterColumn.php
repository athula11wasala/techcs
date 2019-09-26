<?php


namespace App\Repositories\Criteria\Common;

use Bosnadev\Repositories\Contracts\RepositoryInterface as Repository;
use Bosnadev\Repositories\Criteria\Criteria;
use Bosnadev\Repositories\Criteria\RepositoryInterface;

class FilterColumn extends Criteria
{
    /**
     * @var
     */
    private $value;
    private $role;
    /**
     * @var
     */
    private $attribute;

    /**
     * FieldLike constructor.
     * @param $attribute
     * @param $value
     */
    public function __construct($attribute, $value, $role = null)
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
       /* if ($this->attribute == 'renewal_year') {
            $model = $model->whereRaw('Year(users.paid_subscription_end) = ' . $this->value);

        }
        else if ($this->attribute == 'renewal_year_month') {
            $search = explode('-', $this->value);
            $month = $search[0];
            $year = $search[1];
            $model = $model->whereRaw(
                'Year(users.paid_subscription_end) = ' . $year .
                ' AND Month(users.paid_subscription_end) = ' . $month
            );
        }
       */
        if ($this->attribute == 'create_year') {
            $model = $model->whereRaw('Year(users.created_at) = ' . $this->value);

        }
        else if ($this->attribute == 'create_year_month') {
            $search = explode('-', $this->value);
            $month = $search[0];
            $year = $search[1];
            $model = $model->whereRaw(
                'Year(users.created_at) = ' . $year .
                ' AND Month(users.created_at) = ' . $month
            );
        }

        return $model;
    }

}




