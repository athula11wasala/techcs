<?php

namespace App\Repositories;

use App\Equio\Helper;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;


class ReportPurchaseRepository extends Repository
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
        return 'App\Models\ReportPurchase';
    }

    public function allUserStripeDetail($request)
    {

        $this->perPage = env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'id';

        if ( !empty( $request[ 'perPage' ] ) && !empty( $request[ 'perPage' ] = 10 ) ) {
            $this->perPage = $request[ 'perPage' ];
        }
        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );

        return $this->findWhere ( $request );

    }

    public function findWhere($where, $columns = ['*'], $or = false, $elect = false)
    {
        $this->applyCriteria ();

        $model = $this->model;

        foreach ( $where as $field => $value ) {
            if ( $value instanceof \Closure ) {
                $model = (!$or)
                    ? $model->where ( $value )
                    : $model->orWhere ( $value );
            } elseif ( is_array ( $value ) ) {
                if ( count ( $value ) === 3 ) {
                    list( $field, $operator, $search ) = $value;
                    $model = (!$or)
                        ? $model->where ( $field, $operator, $search )
                        : $model->orWhere ( $field, $operator, $search );
                } elseif ( count ( $value ) === 2 ) {
                    list( $field, $search ) = $value;
                    $model = (!$or)
                        ? $model->where ( $field, 'like', '%' . $value . '%' )
                        : $model->orWhere ( $field, '=', $search );
                }
            } else {
                $model = (!$or)
                    ? $model->where ( $field, 'like', '%' . $value . '%' )
                    : $model->orWhere ( $field, '=', $value );
            }
        }

        return $model->orderBy ( $this->sortColumn, $this->sort )
            ->paginate ( $this->perPage );

    }

    public function addReportPurchase($request, $stripe_details_id = null)
    {
        $user_id = \Auth::user ()->id;
        $arr_product = $request[ 'product' ];
        $user_email = \Auth::user ()->email;
        $stripe_details_id = $stripe_details_id;
        $stripeAdd = false;
        $flag = false;
        foreach ( $arr_product as $rows ) {

            if ( isset( $rows[ 'bundle' ] ) ) {

                foreach ( $rows[ 'bundle' ] as $bundle_result ) {

                    if ( !empty( $rows[ 'bundle' ] ) ) {
                        $stripeAdd = $this->model->create ( [
                            'stripe_details_id' => $stripe_details_id,
                            'user_id' => $user_id,
                            'email' => $user_email,
                            'product_id' => $bundle_result[ 'id' ],

                        ] );

                    }


                }

            } else {

                $stripeAdd = $this->model->create ( [
                    'stripe_details_id' => $stripe_details_id,
                    'user_id' => $user_id,
                    'email' => $user_email,
                    'product_id' => $rows[ 'id' ],

                ] );

            }


            if ( $stripeAdd ) {
                $flag = true;
            } else {
                $flag = false;
            }
        }
        return $flag;


    }

}
