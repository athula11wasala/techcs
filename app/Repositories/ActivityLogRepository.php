<?php

namespace App\Repositories;

use App\Models\AuditLog;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use App\Equio\Helper;

class ActivityLogRepository extends Repository
{
    protected $perPage;
    protected $sort;
    protected $sortColumn;
    protected $start_date;
    protected $end_date;
    protected $object;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\AuditLog';
    }

    /**
     * Returns basic comapany info by Name
     * @param $name
     * @return mixed
     */

    public function createActivityLog($data_array)
    {
        $user_id = Auth::user ()->id;
        $action = !empty( $data_array[ 'action' ] ) ? $data_array[ 'action' ] : '';
        $object_id = !empty( $data_array[ 'objectId' ] ) ? $data_array[ 'objectId' ] : 0;
        $object_table = !empty( $data_array[ 'object' ] ) ? $data_array[ 'object' ] : '';
        $object_column = !empty( $data_array[ 'type' ] ) ? $data_array[ 'type' ] : '';
        $location = !empty( $data_array[ 'location' ] ) ? $data_array[ 'location' ] : '';

        if ( !empty( $object_table ) && $object_table == "REPORT" ) {
            if ( !empty( Config::get ( 'custom_config.AUDIT_OBJECT' )[ $data_array[ 'object' ] ] ) ) {
                $object_table = Config::get ( 'custom_config.AUDIT_OBJECT' )[ $data_array[ 'object' ] ];
                if ( $object_table == "reports" ) {
                    $object_column = Config::get ( 'custom_config.AUDIT_REPORT_TYPE' )[ $data_array[ 'type' ] ];
                }

            }

        }

        if ( !empty( $object_table ) && $object_table == "INTERACTIVE_REPORT" ) {
            if ( !empty( Config::get ( 'custom_config.AUDIT_OBJECT' )[ $data_array[ 'object' ] ] ) ) {
                $object_table = Config::get ( 'custom_config.AUDIT_OBJECT' )[ $data_array[ 'object' ] ];
                if ( $object_table == "interactive_reports" ) {
                    $object_column = Config::get ( 'custom_config.AUDIT_REPORT_TYPE' )[ $data_array[ 'type' ] ];
                }

            }

        }


        if ( $this->model->create ( ['user_id' => $user_id, 'action' => $action,
            'object_id' => $object_id, 'object_table' => $object_table,
            'object_column' => $object_column, 'location' => $location] ) ) {

            return true;
        }
        return false;

    }

    public function allActivityLogInfo($request)
    {
        $this->start_date = (!empty( $request[ 'start_date' ] )) ? date ( 'Y-m-d', strtotime ( $request[ 'start_date' ] ) ) : '';
        $this->end_date = (!empty( $request[ 'end_date' ] )) ? date ( 'Y-m-d', strtotime ( $request[ 'end_date' ] ) ) : '';
        $this->keyword = (!empty( $request[ 'keyword' ] )) ? ($request[ 'keyword' ]) : '';
        $this->object_table = (!empty( $request[ 'object_table' ] )) ? ($request[ 'object_table' ]) : '';
        $this->action = (!empty( $request[ 'action' ] )) ? ($request[ 'action' ]) : '';
        $this->perPage = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'audit_log.updated_at';
        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page', 'start_date', 'end_date', 'keyword', 'action', 'object_table'] );

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
                        ? $model->where ( $field, '=', $value )
                        : $model->orWhere ( $field, '=', $search );
                }
            } else {
                $model = (!$or)
                    ? $model->where ( $field, '=', $value )
                    : $model->orWhere ( $field, '=', $value );
            }
        }

        $model = $model->select("audit_log.id", "users.id as user_id", "users.email", "audit_log.action as interaction", 'object_column',
            DB::raw('DATE_FORMAT(audit_log.updated_at, \'%Y-%m-%dT%TZ\') as in_time'), "audit_log.object_table as object_type", "object_id as object_woo_id", "reports.name as object_name")
            ->leftJoin("users", "audit_log.user_id", "users.id")
            ->leftJoin("user_profiles", "user_profiles.user_id", "users.id")
            ->leftJoin("reports", "reports.id", "audit_log.object_id")
            ->leftJoin("interactive_reports", "interactive_reports.report_id", "reports.id");


        if ( !empty( $this->object ) ) {
            $model = $model->where ( "audit_log.action.object_column", '=', $this->object );
        }
        if ( !empty( $this->action ) ) {
            $model = $model->where ( "audit_log.action", '=', $this->action );
        }
        if ( !empty( $this->object_table ) ) {
            $model = $model->where ( "audit_log.object_table", '=', $this->object_table );
        }
        if ( !empty( $this->keyword ) ) {

            $model = $model->Where ( function ($query) {

                  $query->orwhere ( "reports.woo_id", '=', $this->keyword )
                        ->orwhere ( "reports.name", 'like', '%' . $this->keyword . '%' )
                         ->orwhere ( "users.email", 'like', '%' . $this->keyword . '%' )
                        ->orwhere ( "user_profiles.first_name", 'like', '%' . $this->keyword . '%' )
                        ->orwhere ( "audit_log.object_table", 'like', '%' . $this->keyword . '%' )
                        ->orwhere ( "audit_log.location", 'like', '%' . $this->keyword . '%' )
                        ->orwhere ( "audit_log.object_column", 'like', '%' . $this->keyword . '%' );

            } );

        }
        if ( !empty( $this->start_date ) && !empty( $this->end_date ) ) {
            $model = $model->whereRaw ( "Date(audit_log.created_at)>= '" . $this->start_date . "'" )
                           ->whereRaw ( "Date(audit_log.created_at)<=   '" . $this->end_date . "'" );
        }
        return $model
            ->orderBy ( $this->sortColumn, $this->sort )
            ->paginate ( $this->perPage );
    }

}
