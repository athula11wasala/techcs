<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\ZefyrRetailPrice;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;


class ZefyrRetailPriceDetailsRepository extends Repository {
    protected $perPage;
    protected $sort;
    protected $sortColumn;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model() {
        return 'App\Models\ZefyrRetailPrice';
    }

    public function getStateProductInfo() {
        $data['state'] = [];
        $data['product'] = [];
        //$state_zfefer = $this->model->select ('state')->distinct('state')->get();
        $sql_statment = "SELECT state FROM `zefyr_consumer_group` UNION SELECT state FROM zefyr_retail_price";
        $state_zfefer = DB::connection('mysql_external_intake')->select(DB::raw($sql_statment));
        $product = $this->model->select ('product_category')->distinct ('product_category')->get ();
        foreach ( $state_zfefer as $value ) {
            if($value->state == "National"){

                $data[ 'state' ][] = ['id' => $value->state, 'name' => 'U.S.'];
            }else {

                $data[ 'state' ][] = ['id' => $value->state, 'name' => $value->state];
            }


        }
        foreach ( $product as $value ) {
            $data[ 'product' ] [] = ['id' => $value->product_category, 'name' => $value->product_category];
        }
        return $data;
    }

    public function marketData($product_category, $market, $product_category_, $state=null,$sub_type=null) {
        $subType_data = $this->model->select('sub_type', 'state')->where('product_category', $product_category_ );
        if ( !empty( $state ) ) {
            $subType_data = $subType_data->where('state', $state );
        }
        if ( !empty( $sub_type ) ) {
            $subType_data = $subType_data->where('sub_type', $sub_type );
        }
        $subType_data = $subType_data->groupBy('sub_type')->get();
        $ret = [];
        foreach ( $subType_data as $rows ) {
            $strain_data = $this->marketdetails ( $product_category, $market, $rows->sub_type, $state );

            // calculate average price difference percentage
            if(count($strain_data) == 2) {
                $new_price = $strain_data[0]['avg_price'];
                $old_price = $strain_data[1]['avg_price'];
                $difference = $new_price - $old_price;
                $difference_prec = ($difference / (float)$old_price) * 100;
                \Log::info("==== ZefyrRetailPriceDetailsRepository->marketData difference_prec=$difference_prec");
            } else {
                $difference = 0;
                $difference_prec = 0;
            }

            $move = "minus";
            if($difference >= 0.01){
                $move = "caret-up";
            } elseif ($difference <= -0.01) {
                $move = "caret-down";
            }
            if(isset($this->marketdetails ( $product_category, $market, $rows->sub_type, $state )[0])){
                $ret [] = [
                    'strain_name' => $rows->sub_type,
                    'strain_data' => $strain_data,
                    'difference' => $difference,
                    'difference_prec' => $difference_prec,
                    'move' => $move,
                ];
            }
            \Log::info("==== ZefyrRetailPriceDetailsRepository->marketData return");
            \Log::info($ret);
        }
        return $ret;
    }

    public function marketdetails($product_category, $market, $sub_type, $state) {
        $ret = [];
        $arr_date = $this->model->select('date', 'state')->where ('product_category', $product_category);
        if(!empty($state)) {
            $arr_date = $arr_date->where('state', $state );
        }
        $arr_date = $arr_date->where('market', $market )
            ->where('sub_type', $sub_type )
            ->where('sub_type', '!=', 'Gear' )
            ->where('product_category', '!=', 'Topicals' )
            ->orderBy('date', 'desc')
            ->groupBy('zefyr_retail_price.date')
            ->limit(2)->get ();

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
            $sql_statment = "
                SELECT  zefyr_retail_price.date,avg(avg_price)as avg_price, avg(min_price)as min_price, avg(max_price)as max_price, state, quntity_type 
                FROM `zefyr_retail_price`
                WHERE `product_category` = '$product_category' AND market = '$market' AND sub_type = '$sub_type' 
                  AND `sub_type` != 'Gear' AND `product_category` != 'Topicals'
            ";
            if(!empty($state)) {
                $sql_statment .= "AND state = '$state' ";
            }
            $sql_statment.= "AND zefyr_retail_price.date in ($latest_date_) GROUP BY zefyr_retail_price.date ORDER BY zefyr_retail_price.date DESC;";

            $data = DB::connection('mysql_external_intake')->select(DB::raw($sql_statment));
            foreach ( $data as $rows ) {
                if(empty($state)){
                    $display_state = 'all';
                }else {
                    $display_state  = $state;
                }
                if(!empty($state) && !empty($rows->state)){
                    // $display_state = $rows->state;
                }
                $ret[] = [
                    'date' => date('n/d/y', strtotime($rows->date)),
                    'avg_price' => number_format ($rows->avg_price,2),
                    'min_price' =>  number_format ($rows->min_price,2) ,
                    'max_price' =>    number_format ($rows->max_price,2) ,
                    'state' => $display_state,
                    'quantity_type' => $rows->quntity_type,
                ];
            }
        }
        return $ret;
    }

    public function productData($product_category, $state = null,$market= null,$sub_type= null) {
        $product_data = $this->model->selectRaw('market, product_category, state')->where('product_category', $product_category );
        if ( !empty( $state ) ) {
            $product_data = $product_data->where('state', $state );
        }
        if ( !empty( $market ) ) {
            $product_data = $product_data->where('market', $market );
        }
        if ( !empty( $sub_type ) ) {
            $product_data = $product_data->where('sub_type', $sub_type );
        }
        $product_data = $product_data->where('sub_type', '!=', 'Gear' );
        $product_data = $product_data->where('product_category', '!=', 'Topicals' );
        $product_data = $product_data->groupBy('market')->get();
        $ret = [];
        foreach ( $product_data as $rows ) {
            $market = $rows->market;
            if($rows->market == "recreational"){
                $market= "adults";
            }
            $ret[] = [
                'market_name' => ucwords($market),
                'market_data' => $this->marketData($product_category, $rows->market, $rows->product_category, $state ,$sub_type)
            ];
        }
        return $ret;
    }

    public function allRetailPriceDetailInfo($request) {
        $this->perPage = env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'zefyr_retail_price.date';
        $this->state = (!empty( $request[ 'state' ] )) ? ($request[ 'state' ]) : 'National';
        $this->product_category = (!empty( $request[ 'product_category' ] )) ? ($request[ 'product_category' ]) : '';
        $this->market = (!empty( $request[ 'market' ] )) ? ($request[ 'market' ]) : '';
        $this->sub_type = (!empty( $request[ 'sub_type' ] )) ? ($request[ 'sub_type' ]) : '';
        if ( !empty( $request[ 'perPage' ] ) ) {
            $this->perPage = $request[ 'perPage' ];
        }
        $ret = [];
        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );
        $data = $this->searchWhere ( $request );

        $product_category = $this->model->select('product_category');

        if ( !empty( $this->product_category ) ) {
            $product_category = $product_category->where('product_category', "$this->product_category" );
        }
        if ( !empty( $this->state ) ) {
            $product_category = $product_category->where('state', "$this->state" );
        }
        if ( !empty( $this->market ) ) {
            $product_category = $product_category->where('market', "$this->market" );
        }
        if ( !empty( $this->sub_type ) ) {
            $product_category = $product_category->where('sub_type', "$this->sub_type" );
        }
        $product_category = $product_category->where('sub_type', '!=', 'Gear' );
        $product_category = $product_category->where('product_category', '!=', 'Topicals' );
        $product_category = $product_category->groupBy('product_category')->get ();
        foreach ( $product_category as $rows ) {
            $ret[] = [
                'product_type' => $rows->product_category,
                'product_data' => $this->productData($rows->product_category, $this->state, $this->market, $this->sub_type )
            ];
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
        $ret = $model->get();
        return $ret;
    }

}