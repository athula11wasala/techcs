<?php


namespace App\Repositories\Criteria\Users;

use Bosnadev\Repositories\Criteria\Criteria;
use Bosnadev\Repositories\Contracts\RepositoryInterface as Repository;
use Bosnadev\Repositories\Criteria\RepositoryInterface;

class FilterByCount extends Criteria
{
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

        if ($value->inTrial) {
            $now = date('Y-m-d H:i:s');
            $model = $model->whereRaw ( "Date(users.paid_subscription_start)= Date(users.trial_datetime)" );
            $model = $model->whereRaw ( "Date(paid_subscription_start)<= '" . $now . "'" );
            $model = $model->whereRaw ( "Date(paid_subscription_end)>= '" . $now . "'" );
            $model = $model->where('disable', '=', "0");
          //  $model = $model->whereColumn('trial_datetime', '=', 'paid_subscription_start');
        }
        if ($value->paid) {
            $now = date('Y-m-d H:i:s');
            $model =$model->whereRaw ( "Date(users.paid_subscription_start)!= Date(users.trial_datetime)" )
                //$model->whereColumn('trial_datetime', '!=', 'paid_subscription_start')
                ->where('paid_subscription_start', '<=', $now)
                ->where('paid_subscription_end', '>=', $now);
            $model = $model->where('disable', '=', "0");
        }

        if ($value->expired) {
            $now = date('Y-m-d H:i:s');
            $model = $model->where('paid_subscription_end', '<=', $now)->where('disable', '=', "0");
        }

        if ($value->disabled) {
            $model = $model->where('disable', '=', "1");
        }

        return $model;
    }
}
