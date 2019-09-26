<?php

namespace App\Repositories;

use App\Models\DataSet;
use App\Models\QualifyingCondition;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use App\Equio\Helper;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;


class QualifyConditionRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\QualifyingCondition';
    }


    public function getFirstRowColumValue($name)
    {
        $results = DB::Connection ( "mysql_external_intake" )->table ( "qualifying_conditions" )->select ( $name )->first ();
        return !empty( $results->$name ) ? $results->$name : '';
    }


    public function qalifyConditionInfoByState($request)
    {

        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page', 'id', 'dataset_id'] );

        $result = $this->findWhere ( $request );

        $data = [];
        $allState = $this->getAllState ();
        $allColumnName = $this->getTableColumns ();
        unset( $allColumnName[ 0 ] );
        unset( $allColumnName[ 1 ] );

        if ( count ( $result ) == 0 ) {

            return $data;
        }

        foreach ( $result as $rows ) {

            foreach ( $allState as $stateName ) {

                if ( $stateName->state == $rows->state ) {

                    foreach ( $allColumnName as $columnName ) {
                        $name = $columnName->Field;
                        if ( $rows->$name == "T" ) {


                            $data[ $stateName->state ][ 'name' ][] = $this->getFirstRowColumValue ( $columnName->Field );  // $name;
                            $data[ $stateName->state ][ 'count' ] = count ( $data[ $stateName->state ][ 'name' ] );

                        }

                    }


                }

            }

        }

        foreach ( $data as $state => $value ) {
            $count[ $state ] = $value[ 'count' ];
            $names[ $state ] = $value[ 'name' ];
        }
        // filter by array value decending
        arsort ( $count );
        foreach ( $count as $sts => $cunt ) {
            $sortedData[ $sts ][ 'name' ][] = $names[ $sts ];
            $sortedData[ $sts ][ 'count' ] = $cunt;
        }

        return $sortedData;

    }


    public function filterQalifyConditionInfo($request)
    {
        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page', 'id', 'dataset_id'] );
        $result = $this->findWhere ( $request );

        $data = [];
        $allState = $this->getAllState ();
        $allColumnName = $this->getTableColumns ();
        unset( $allColumnName[ 0 ] );
        unset( $allColumnName[ 1 ] );
        $deleteColName = array("created_at", "updated_at", "dataset_id");
        $data[] = ['id' => '', 'value' => 'Select One'];

        foreach ( $allColumnName as $columnName ) {
            $name = $columnName->Field;

            if ( !in_array ( $name, $deleteColName ) ) {

                $data[] = ['id' => $name, 'value' => Helper::stringChange ( $name )];

            }
        }

        return $data;

    }


    public function findWhere($where, $columns = ['*'], $or = false, $elect = false)
    {

        $this->applyCriteria ();

        $objHelper = new Helper();
        $datasetId = Config::get ( 'custom_config.data_set' )[ 'QualifyingConditions' ];

        $model = $this->model
            ->select (
                [
                    "*"
                ]
            )
            ->where ( "dataset_id", $objHelper->getDataSetId (  QualifyingCondition::$table_connection, $datasetId ) )
            ->where ( "dataset_id", "!=", 0 )
            ->where ( "dataset_id", "!=", '' )
            ->where ( "latest", "=", 1 )
            ->where ( 'state', '!=', '' )
            ->orderBy ( "id", "asc" );


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
                    ? $model->where ( $field, '=', 'T' )
                    : $model->orWhere ( $field, '=', 'T' );
            }
        }

        return $model->get ();

    }


    public function getAllState()
    {

        return $this->model
            ->select (
                [
                    "state"
                ]
            )
            ->where ( 'state', '!=', '' )
            ->orderBy ( "id", "asc" )
            ->get ();
    }

    public function getTableColumns()
    {
        return DB::Connection ( "mysql_external_intake" )->select (
            DB::raw ( 'show columns from qualifying_conditions' )
        );
    }


}