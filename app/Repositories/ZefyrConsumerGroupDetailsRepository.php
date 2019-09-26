<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\ZefyrConsumerGroup;
use App\Models\ZefyrConsumerTapestry;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;


class ZefyrConsumerGroupDetailsRepository extends Repository {
    protected $perPage;
    protected $sort;
    protected $sortColumn;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model() {
        return 'App\Models\ZefyrConsumerGroup';
    }
    public function modelTapestry() {
        return 'App\Models\ZefyrConsumerTapestry';
    }

    public function getStateProductInfo() {
        $data[ 'state' ] = [];
        $state_zefyr = $this->model->select('state')->distinct('state')->get ();
        foreach ( $state_zefyr as $value ) {

            $data[ 'state' ][] = ['id' => $value->state,
                'name' => $value->state];
        }
        return $data;
    }

    public function stateData($state = null) {
        $subType_data = $this->model->select('zefyr_consumer_group.consumer_group');
        $ret = [];
        if ( !empty( $state ) ) {
            $subType_data = $subType_data->where ( "state", $state );

        } else {
            return $ret;
        }
        $consumer_group_data = $subType_data->groupBy ( "consumer_group" )->get ();
        foreach ( $consumer_group_data as $rows ) {
            $ret[] = ['segment_name' => $rows->consumer_group,
                'segment_data' => $this->statedetails ( $state, $rows->consumer_group )];
        }
        return $ret;
    }

    public function getConsumerImage($value){
        $data =  $this->model->select(["zefyr_consumer_group.consumer_group as consumer_image",])
            ->distinct ( 'consumer_group')
            ->where('consumer_group',$value)
            ->get ();
        return $data[0]->consumer_image;
    }

    public function getConsumerTapestry($value) {
        $data = DB::connection('mysql_external_intake')->select(DB::raw("
                SELECT  median_income, top_professions FROM zefyr_consumer_tapestry
                WHERE  consumer_group = '$value';
            "));
        if(count($data)>0){
            return $data[0];
        }
        return null;
    }

    public function statedetails($state, $consumer_group) {
        $arr_date = $this->model->select('zefyr_consumer_group.date')
            ->where('consumer_group', $consumer_group)
            ->where('state', $state)
            ->orderBy('date', 'desc')
            ->groupBy('zefyr_consumer_group.date')->limit(1)->get ();

        $latest_date_ = null;
        $num = 0;
        foreach ( $arr_date as $rows ) {
            if ( $num == 0 ) {
                $latest_date_ .= "'" . $rows->date . "'";
            } else {
                $latest_date_ .= ",'" . $rows->date . "'";
            }
            $num++;
        }
        if ( !empty( $latest_date_ ) ) {
            $data = DB::connection('mysql_external_intake')->select(DB::raw("
                SELECT  zefyr_consumer_group.* FROM zefyr_consumer_group
                WHERE  consumer_group = '$consumer_group' AND state = '$state'
                AND zefyr_consumer_group.date IN ($latest_date_) GROUP BY zefyr_consumer_group.date;
            "));
            foreach ( $data as $rows ) {
                $tapestry = $this->getConsumerTapestry( $rows->consumer_group);
                $median_income= '';
                $top_professions='';
                if(!empty($tapestry)) {
                    $median_income = isset($tapestry->median_income)?$tapestry->median_income:'';
                    $top_professions = '';
                    if(isset($tapestry->top_professions)) {
                        $professions = explode(" | ", $tapestry->top_professions);
                        if(count($professions) > 3) {
                            $professions = array_slice($professions, 0, 3);
                        }
                        $top_professions = implode(", ", $professions);
                    }
                }
                $ret = [
                    'date' => $rows->date,
                    'male_pop' => $rows->male_pop,
                    'female_pop' => ($rows->female_pop),
                    'adult_dispensary_consumer' => ($rows->adult_dispensary_consumer),
                    'adult_dispensary_total' => ($rows->adult_dispensary_total),
                    'medical_dispensary_consumer' => $rows->medical_dispensary_consumer,
                    'medical_dispensary_total' => $rows->medical_dispensary_total,
                    'hybrid_dispensary_consumer' => ($rows->hybrid_dispensary_consumer),
                    'hybrid_dispensary_total' => $rows->hybrid_dispensary_total,
                    'image'=>str_replace("'","_",str_replace("’","_",($this->getConsumerImage($rows->consumer_group)))),
                    'median_income'=>$median_income,
                    'top_professions'=>$top_professions
                ];
            }
        }
        return $ret;
    }

    public function allConsumerGroupDetailInfo($request) {
        $this->perPage = env('PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'zefyr_consumer_group.date';
        $this->state = (!empty( $request[ 'state' ] )) ? ($request[ 'state' ]) : '';
        if ( !empty( $request[ 'perPage' ] ) ) {
            $this->perPage = $request[ 'perPage' ];
        }
        $ret = [];
        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );
        $data = $this->searchWhere ( $request );
        $product_category = $this->model->select('state');
        if(!empty($this->state)) {
            $product_category = $product_category->where('state', $this->state );
            $product_category = $product_category->groupBy('state')->get();
            if (count($product_category ) > 0) {
                $ret = [
                    'state' => $product_category[0]->state,
                    'state_data' => $this->stateData($product_category[ 0 ]->state)
                ];
            } else {
                return $ret;
            }
        }
        return $ret;
    }

    public function searchWhere($where, $columns = ['*'], $or = false, $elect = false) {
        $this->applyCriteria ();
        $model = $this->model->select (["*"]);
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
                        ? $model->Where ( $field, '=', $value )
                        : $model->orWhere ( $field, '=', $search );
                }
            } else {
                $model = (!$or)
                    ? $model->Where ( $field, '=', $value )
                    : $model->orWhere ( $field, '=', $value );
            }
        }
        if ( !empty( $this->state ) ) {
            $model = $model->where ( 'state', $this->state );
        }
        if ( !empty( $this->product ) ) {
            $model = $model->where ( 'product_category', $this->product );
        }
        $model = $model->orderBy ( $this->sortColumn, $this->sort );
        //->groupBy('date');
        return $model->get ();
    }

}