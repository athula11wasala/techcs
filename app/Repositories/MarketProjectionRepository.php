<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Config;
use Elasticsearch\ClientBuilder;
use DB;
use \Datetime;

class MarketProjectionRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model() {
        return 'App\Models\MarketProjection';
    }

    /**
     * Returns the chart data
     * @return mixed
     */
    public function getCharts() {
        $result = DB::connection('mysql_external_intake')->select(DB::raw("
            SELECT *
            FROM `market_projection` 
            WHERE market_projection.country='US';
        "));
        $medical = array();
        $recreational = array();
        $total_legal = array();
        $total_illicit = array();
        foreach ($result as $bucket) {
            $bucket = (array) $bucket;
            $categories[] = ['label'=>date("Y",strtotime($bucket['date']))];
            $medical[] = ['value'=>number_format(((float)$bucket['medical'] / 1e9), 1)];
            $recreational[] = ['value'=>number_format(((float)$bucket['recreational'] / 1e9), 1)];
            $total_legal[] = ['value'=>number_format(((float)$bucket['total_legal'] / 1e9), 1)];
            $total_illicit[] = ['value'=>number_format(((float)$bucket['total_illicit'] / 1e9), 1)];
        }
        $multi = ['categories'=>$categories, 'medical'=>$medical, 'recreational'=>$recreational, 'total_legal'=>$total_legal, 'total_illicit'=>$total_illicit];
        return $multi;
    }
}