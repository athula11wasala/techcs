<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use App\Equio\Helper;

class JobsRepository extends Repository
{


    Protected $perPage;
    Protected $sort;
    Protected $sortColumn;
    Protected $limit;
    Protected $state;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\Job';
    }


    public function allJobInfo($request)
    {

        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'jobs.updated_at';
        $this->limit = (!empty( $request[ 'limit' ] )) ? ($request[ 'limit' ]) : 100;
        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );
        $columns = ['*'];
        $or = false;

        $data[ 'data' ] = [];

        $model = $this->model
            ->select (
                [
                    'id'
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

        $result = $model
            ->where ( 'title', "!=", '' )->where ( "description", "!=", '' )
            ->where ( 'status', 1 )->where ( "state", "!=", '' )
            ->where ( "provider", "!=",'' )
            ->orderBy ( $this->sortColumn, $this->sort )
            // ->orderBy ( DB::raw ( 'FIELD(provider, "vangsters", "weedhire", "indeed")' ) )
            // ->limit ( $this->limit )
            ->get ();

        $arrId = [];
        foreach ( $result as $rows ) {
            $arrId[] = $rows->id;
        }

        $model = $this->model
            ->select (
                [
                    '*'
                ] )
            ->whereIn ( "id", $arrId )
            ->where ( "provider", "!=",'' )
            ->orderBy ( DB::raw ( 'FIELD(provider, "vangsters", "weedhire", "indeed")' ) )
            ->orderBy ( $this->sortColumn, $this->sort )
            ->orderBy ( 'id', $this->sort )
            ->paginate ($this->limit );
          
        $data[ 'data' ] = $model;
        return $data;
    }

    public function getAllStatus()
    {

        $result = $this->model
            ->select (
                [
                    'state',
                ] )
            ->groupBy ( 'state' )
            ->where ( "state", "!=", '' )
            ->get ();
        $data = [];
        foreach ( $result as $rows ) {

            $data[ $rows->state ] = $rows->state;

        }
        return $data;

    }


    public function allMapInfo($request)
    {
        $this->perPage = env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn = (!empty( $request[ 'state' ] )) ? ('state') : 'updated_at';
        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );

        $result = $this->findWhere ( $request );

        $data = [];

        $allStatus = $this->getAllStatus ();
        $i = 0;

        foreach ( $result as $rows ) {

              if( !isset ($data[ $rows->state ][ 'detail' ][ 'count' ])){
                  $data[ $rows->state ][ 'detail' ][ 'count' ] = $this->stateCount ( $rows->state );
              }

        }
        return $data;

    }

    public function allMapInfoByState($request)
    {

        $this->perPage = env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn =  'updated_at';
        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );
        $this->state  =(!empty( $request[ 'state' ] )) ? ($request[ 'state' ]) : '0';

        $result = $this->findWhereState ( $request );

        $data = [];

        $allStatus = $this->getAllStatus ();
        $i = 0;

        foreach ( $result as $rows ) {

            $provider = "";
            if ( $rows->provider == "vangsters" ) {

                $provider = "vangst";
            }

            foreach ( $allStatus as $status ) {

                if ( $status == $rows->state ) {

                    $data[ 'detail' ][] = ['title' => $rows->title, 'description' => $rows->description,
                        'link' => $rows->link, 'provider' => $provider];

                    if ( count ( $data[ 'detail' ] ) == 1 ) {
                        $data[ 'count' ] = 1;
                    } else {

                        $data[ 'count' ] = (count ( $data[ 'detail' ] ) - 1);
                    }

                }
            }

        }
        return $data;

    }


    public function stateCount($state)
    {
        $result = $result = DB::connection ( 'mysql_external_intake' )->select ( DB::raw ( "
               SELECT count(id) as id  FROM jobs where state = '$state' and status= '1' and  status != ''  
        " ) );

        if ( isset( $result[ 0 ]->id ) ) {

            return $result[ 0 ]->id;
        } else {

            return 0;
        }


    }

    public function findWhereState($where, $columns = ['*'], $or = false, $elect = false)
    {
        $this->applyCriteria ();

        $model = $this->model
            ->select (
                [
                    "id", "title", "description", "link", "city", "state", "provider"
                ] );




        //$model = $model->orderBy ( $this->sortColumn, $this->sort );
        $model = $model->where ( 'status', 1 )->where ( "state", "!=", '' )->where ( "state", "=", "$this->state" );
        $model = $model->orderBy ( DB::raw ( 'FIELD(provider, "vangsters", "weedhire", "indeed")' ) );
        return $model->get ();



    }



    public function findWhere($where, $columns = ['*'], $or = false, $elect = false)
    {
        $this->applyCriteria ();

        $model = $this->model
            ->select (
                [
                    "id", "title", "description", "link", "city", "state", "provider"
                ] );


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

        $model = $model->orderBy ( $this->sortColumn, $this->sort );
        $model = $model->where ( 'status', 1 )->where ( "state", "!=", '' );
        return $model->get ();

    }

}
