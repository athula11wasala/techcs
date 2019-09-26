<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\FeatureAlert;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;


class UserAcknowledgementRepository extends Repository
{


    protected $perPage;
    protected $sort;
    protected $sortColumn;
    protected $limit;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\UserAcknowledgement';
    }


    /**
     * Returns all feature alert info
     *
     * @param $request
     * @return mixed
     */
    public function getAllAlert($request)
    {
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->perPage = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '15';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'updated_at';
        $this->limit = (!empty( $request[ 'limit' ] )) ? ($request[ 'limit' ]) : 100;

        $columns = ['*'];
        $or = false;
        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );


        $model = $this->model->select (
            "id",
            "title",
            "description",
            "image",
            "link",
            "active"
        );

        foreach ( $request as $field => $value ) {
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


        $result = $model
            ->orderBy ( $this->sortColumn, $this->sort )
            ->paginate ( $this->perPage );

        if ( count ( $result ) == 0 ) {
            return false;
        }

        return $result;
    }


    /**
     * @param $alert_array
     * @return bool
     */
    public function saveAlert($alert_array)
    {
        $title = !empty( $alert_array[ 'title' ] ) ? $alert_array[ 'title' ] : 0;
        $description = !empty( $alert_array[ 'description' ] ) ? $alert_array[ 'description' ] : '';
        $link = !empty( $alert_array[ 'link' ] ) ? $alert_array[ 'link' ] : '';
        $active = !empty( $alert_array[ 'active' ] ) ? $alert_array[ 'active' ] : 0;
        $order = !empty( $alert_array[ 'order' ] ) ? $alert_array[ 'order' ] : 0;


        $featureAlert = $this->model->create ( [
            'title' => $title,
            'description' => $description,
            'image' => '',
            'link' => $link,
            'active' => $active,
            'order' => $order
        ] );

        return $this->uploadAlertImage ( $alert_array, 'POST', $featureAlert->id );

    }


    public function saveUserAcknowledgements($arr)
    {

        $id = !empty( $arr[ 'id' ] ) ? $arr[ 'id' ] : 0;

        DB::table ( "user_acknowledgements" )->where ( ['user_id' => Auth::user ()->id, 'feature_id' => $id] )->delete ();

        return $this->model->create ( ['user_id' => Auth::user ()->id, 'feature_id' => $id] );


    }


}
