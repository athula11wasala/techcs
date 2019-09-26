<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Config;
use Elasticsearch\ClientBuilder;
use DB;
use \Datetime;

class PatientCountRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model() {
        return 'App\Models\PatientCount';
    }

    /**
     * Returns the chart data
     * @return mixed
     */
    public function getChartsOld() {
        $states = DB::connection('mysql_external_intake')->select(DB::raw("
            SELECT state FROM `patient_count_projection_us` 
        "));
        $multi = array();
        foreach($states as $state_elt) {
            $state = $state_elt->state;
            $result = DB::connection('mysql_external_intake')->select(DB::raw("
                SELECT patient_count_us.Date,
                    IF(patient_count_projection_us.projection_start_date>=patient_count_us.Date,patient_count_us.$state,'NULL') as '$state',
                    IF(patient_count_projection_us.projection_start_date<=patient_count_us.Date,patient_count_us.$state,'NULL') as 'est_$state'
                FROM `patient_count_us`, `patient_count_projection_us` 
                WHERE patient_count_projection_us.state='$state' AND patient_count_us.Date > '2013-12-01';
            "));
            $series_mes = array();
            $series_est = array();
            foreach ($result as $bucket) {
                $bucket = (array) $bucket;
                if($bucket[$state] && $bucket[$state] != "NULL"){
                    $measured['name'] = date("Y-m-d h:i:s",strtotime($bucket['Date']));
                    $measured['value'] = $bucket[$state]/1000000;
                    $series_mes[] = $measured;
                }
                if($bucket['est_' . $state] && $bucket['est_' . $state] != "NULL") {
                    $estimated['name'] = date("Y-m-d h:i:s", strtotime($bucket['Date']));
                    $estimated['value'] = $bucket['est_' . $state]/1000000;
                    $series_est[] = $estimated;
                }
            }
            $data_mes = array();
            $data_mes['name'] = $state.'_me';
            $data_mes['series'] = $series_mes;
            $multi[$state.'_mes'] = $data_mes;
            $data_est['name'] = $state.'_es';
            $data_est['series'] = $series_est;
            $multi[$state.'_est'] = $data_est;
        }
        //\Log::info("==== PatientCountRepository->getCharts ", ['u' => json_encode($multi, JSON_PRETTY_PRINT)]);
        return $multi;
    }
    public function getCharts() {
        $states = DB::connection('mysql_external_intake')->select(DB::raw("
            SELECT state FROM `patient_saturation_projection_us`
        "));
        $dataset = array();
        foreach($states as $state_elt) {
            $state = $state_elt->state;
            $result = DB::connection('mysql_external_intake')->select(DB::raw("
                SELECT patient_count_us.Date,
                    patient_count_us.$state as value,
                    IF(patient_count_projection_us.projection_start_date>=patient_count_us.Date,0,1) as dashed
                FROM `patient_count_us`, `patient_count_projection_us` 
                WHERE patient_count_projection_us.state='$state' AND patient_count_us.Date > '2014-01-01';
            "));
            $category = array();
            $data = array();
            foreach ($result as $bucket) {
                $bucket = (array) $bucket;
                $category[] = ['label'=>date("Y", strtotime($bucket['Date']))];
                $value = NULL;
                if(is_numeric($bucket['value'])){
                    $value = (float)$bucket['value']/1000000;
                }
                $data[] = [
                    'value'=>$value,
                    'dashed'=>(integer)$bucket['dashed']
                ];
            }
            $visible = 0;
            if($state == "CO" || $state == "AZ") {
                $visible = 1;
            }
            $dataset[] = ['seriesname'=>$state, 'data'=>$data, 'visible'=>$visible];
        }
        $multi = ['category'=>$category, 'dataset'=>$dataset];
        return $multi;
    }
}