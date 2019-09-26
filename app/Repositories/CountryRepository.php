<?php

namespace App\Repositories;

use App\Models\State;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use App\Equio\Helper;


class CountryRepository extends Repository
{


    Protected $perPage;
    Protected $sort;
    Protected $sortColumn;
    Protected $limit;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\Country';
    }


    public function allCountryInfo($request)
    {
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'asc';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'countries.name';
        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );

        $columns = ['*'];
        $or = false;
        $elect = false;

        $data[ 'data' ] = [];

        $model = $this->model
            ->select (
                [
                    'id', 'name','sortname'
                ] );

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
        $model = $model->orderBy ( $this->sortColumn, $this->sort )
            ->get ();


        $data = $model;
        return $data;
    }

    public function allStateInfo($request)
    {
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'asc';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'states.name';
        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );

        $columns = ['*'];
        $or = false;
        $elect = false;

        $data[ 'data' ] = [];

        $this->model = new State();
        $model = $this->model
            ->select (
                [
                    'id', 'name'
                ] );

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
                        ? $model->orWhere ( $field, '=', $value )
                        : $model->orWhere ( $field, '=', $search );
                }
            } else {
                $model = (!$or)
                    ? $model->orWhere ( $field, '=', $value )
                    : $model->orWhere ( $field, '=', $value );
            }
        }
        $model = $model->orderBy ( $this->sortColumn, $this->sort )
            ->get ();

        $data = $model;
        return $data;
    }

    public function countrPhoneCodeInfo($id)
    {
        $this->id = (!empty( $id )) ? ($id) : 0;
        return $this->model
            ->select (
                [
                    'phonecode'
                ] )->where ( "id", $this->id )->first ();
    }

}
