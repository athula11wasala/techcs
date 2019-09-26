<?php

namespace App\Repositories;

use App\Equio\Helper;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;


class UserStripeRepository extends Repository
{


    Protected $perPage;
    Protected $sort;
    Protected $sortColumn;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\UserStripe';
    }

    public function allUserStripeDetail($request)
    {

        $this->perPage = env('PAGINATE_PER_PAGE', 15);
        $this->sort = (!empty($request['sort'])) ? ($request['sort']) : 'desc';
        $this->sortColumn = (!empty($request['sortType'])) ? ($request['sortType']) : 'id';

        if (!empty($request['perPage']) && !empty($request['perPage'] = 10)) {
            $this->perPage = $request['perPage'];
        }
        $request = Helper::arrayUnset($request, ['sortType', 'perPage', 'sort', 'page']);

        return $this->findWhere($request);

    }

    public function findWhere($where, $columns = ['*'], $or = false, $elect = false)
    {
        $this->applyCriteria();

        $model = $this->model;

        foreach ($where as $field => $value) {
            if ($value instanceof \Closure) {
                $model = (!$or)
                    ? $model->where($value)
                    : $model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);
                } elseif (count($value) === 2) {
                    list($field, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, 'like', '%' . $value . '%')
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (!$or)
                    ? $model->where($field, 'like', '%' . $value . '%')
                    : $model->orWhere($field, '=', $value);
            }
        }

        return $model->orderBy($this->sortColumn, $this->sort)
            ->paginate($this->perPage);

    }

    public function addUserStripe($request,$stripe_status= null,$transaction_id= null) {

        $user_id = \Auth::user()->id;
        $stripe_status = !empty($stripe_status)?1 : 0;
        $transaction_id = $transaction_id;
        $stripeAdd = $this->model->create([
            'user_id' =>  $user_id,
          //  'product_id' => $product_id,
            'stripe_status' => $stripe_status,
            'transaction_id' => $transaction_id
        ]);

        if ($stripeAdd) {
            return $stripeAdd->id;
        } else {
            return false;
        }
    }


    public function updateUserStripe($id,$status) {

        $id = $id;
        $woo_status = $status;

        $stripeUpdate = $this->model->where('id', '=', $id)
            ->update(['woo_status' => $woo_status]);

        if ($stripeUpdate) {
            return true;
        } else {
            return false;
        }
    }





}
