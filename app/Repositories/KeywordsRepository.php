<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use App\Equio\Helper;

class KeywordsRepository extends Repository
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
        return 'App\Models\Keyword';
    }


    public function allKeywordsInfo()
    {
        return $this->model->select ( "id", "name" )->get ();
    }

    public function saveKeyWord($data_array)
    {

        $name = !empty( $data_array[ 'name' ] ) ? $data_array[ 'name' ] : '';
        $objKeyWord = $this->model->create ( ['name' => $name] );

        return !empty( $objKeyWord ) ? true : false;

    }

    public function updateKeyWord($data_array)
    {

        $name = !empty( $data_array[ 'name' ] ) ? $data_array[ 'name' ] : '';
        $objKeyWord = $this->model->where ( 'id', '=', $data_array[ 'id' ] )
            ->update ( ['name' => $name] );

        return !empty( $objKeyWord ) ? true : false;

    }

    public function deleteKeyWord($request)
    {
        return $this->model->where ( 'id', '=', $request[ 'id' ] )
            ->delete ();
    }

    /**
     * Returns all comapany info
     *
     * @return mixed
     */
    public function allKeyWordDetailPaginate($request)
    {
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->perPage = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '15';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'updated_at';
        $this->limit = (!empty( $request[ 'limit' ] )) ? ($request[ 'limit' ]) : 100;
        $columns = ['*'];
        $or = false;

        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );

        $model = $this->model
            ->select ( "id", "name" );

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
             ->where("name","!=", "")
            ->orderBy ( $this->sortColumn, $this->sort )
            ->paginate ( $this->perPage );

        if ( count ( $result ) == 0 ) {
            return false;
        }

        return $result;
    }


    /**
     * Returns all comapany info
     *
     * @return mixed
     */
    public function KeyWordDetail($request)
    {
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->perPage = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '15';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'updated_at';
        $this->limit = (!empty( $request[ 'limit' ] )) ? ($request[ 'limit' ]) : 100;
        $columns = ['*'];
        $or = false;

        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );

        $model = $this->model
            ->select ( "id", "name" );

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
            ->where("name","!=", "")
            ->orderBy ( $this->sortColumn, $this->sort )
            ->get (  );

        if ( count ( $result ) == 0 ) {
            return false;
        }

        return $result;
    }

}
