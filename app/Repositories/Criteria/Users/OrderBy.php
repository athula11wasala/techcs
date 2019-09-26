<?php

namespace App\Repositories\Criteria\Users;

use Bosnadev\Repositories\Criteria\Criteria;
use Bosnadev\Repositories\Contracts\RepositoryInterface as Repository;

class OrderBy extends Criteria
{

    private $order;
    private $orderColumn;

    /**
     * OrderByCreated constructor.
     * @param $order
     * @param $orderColumn
     */
    public function __construct($order, $orderColumn)
    {
        $this->order = $order;
        $this->orderColumn = $orderColumn;
    }

    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
         if($this->orderColumn == "last_signed_in_date"){

             $model =  $model->orderByRaw('DATE(users.last_sign_in_at)' . $this->order);

         }
        else if($this->orderColumn == "created_date"){

            $model =  $model->orderByRaw('DATE(users.created_at)' . $this->order);

        }
        else if($this->orderColumn == "renewal_date"){
            $model =  $model->orderByRaw('DATE(users.paid_subscription_end)' . $this->order);

        }
         else {

             $model = $model->orderBy($this->orderColumn, $this->order);

         }

        return $model;
    }
}