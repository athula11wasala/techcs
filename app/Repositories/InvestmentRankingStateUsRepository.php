<?php

namespace App\Repositories;

use App\Equio\Helper;
use Bosnadev\Repositories\Eloquent\Repository;
use App\Models\InvestmentRankingStateUs;
use DB;
use Illuminate\Support\Facades\Config;
use App\Models\DataSet;
use Illuminate\Support\Collection;


class InvestmentRankingStateUsRepository extends Repository
{

    protected $perPage;
    protected $sort;
    protected $sortColumn;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\InvestmentRankingStateUs';
    }


    public function getInvestmentRankdDetails()
    {
        $objHelper = new Helper();
        $datasetId = Config::get ( 'custom_config.data_set' )[ 'investment_ranking_threshold_us' ];
        $data = [];

        $state = $this->getStateName ();
        $result = $this->model
            ->select (
                [
                    "*"
                ] )
            ->where ( "dataset_id",
                $objHelper->getDataSetId ( InvestmentRankingStateUs::$table_connection, $datasetId ) )
            ->where ( "dataset_id", "!=", 0 )
            ->where ( "dataset_id", "!=", '' )
            ->where ( "latest", "=", 1 )
            ->get ();

        foreach ( $result as $rows ) {

            $data[ $rows->state ][ 'cultivation' ] = $this->getColorCode ( $rows->cultivation, "cultivation" );
            $data[ $rows->state ][ 'retail' ] = $this->getColorCode ( $rows->retail, "retail" );
            $data[ $rows->state ][ 'manufacturing' ] = $this->getColorCode ( $rows->manufacturing, "manufacturing" );
            $data[ $rows->state ][ 'distribution' ] = $this->getColorCode ( $rows->distribution, "distribution" );
            $data[ $rows->state ][ 'ancillary' ] = $this->getColorCode ( $rows->ancillary, "ancillary" );
            $data[ $rows->state ][ 'risk' ] = $this->getColorCode ( $rows->risk, "risk" );
            $data[ $rows->state ][ 'opportunity' ] = $this->getColorCode ( $rows->opportunity, "opportunity" );
            $data[ $rows->state ][ 'opportunity' ][ 'value' ] = $rows->opportunity;
            $data[ $rows->state ][ 'legalization' ] = $rows->legalization;
            $data[ $rows->state ][ 'state' ] = $state[ $rows->state ];

        }
        return $data;
    }


    public function sortInvestmentRankdByCode()
    {
        $objHelper = new Helper();
        $datasetId = Config::get ( 'custom_config.data_set' )[ 'investment_ranking_threshold_us' ];
        $data = collect ();
        $state = $this->getStateName ();

        $result = $this->model
            ->select (
                [
                    "*"
                ] )
            ->where ( "dataset_id",
                $objHelper->getDataSetId ( InvestmentRankingStateUs::$table_connection, $datasetId ) )
            ->where ( "dataset_id", "!=", 0 )
            ->where ( "dataset_id", "!=", '' )
            ->where ( "latest", "=", 1 )
            ->get ();

        foreach ( $result as $rows ) {

            if ( $rows->opportunity ) {
                $retsult = $this->getColorCode ( $rows->opportunity, "opportunity" );
                $data->push(['value' => $rows->opportunity, 'color' => $retsult[ 'color' ], 'state' => $state[ $rows->state ]]);
            }

        }
        $sorted = $data->sortByDesc ( 'value' );

        return $sorted->values ()->all ();
    }

    public function getColorCode($value = null, $column = null)
    {
        $objHelper = new Helper();
        $datasetId = Config::get ( 'custom_config.data_set' )[ 'investment_ranking_threshold_us' ];
        $data = ['color' => '', 'text' => ''];

        $result = DB::Connection ( "mysql_external_intake" )->table ( "investment_ranking_threshold_us" )
            ->select ( "low_medium", "medium_high", "segment" )
            ->where ( "dataset_id", $objHelper->getDataSetId ( InvestmentRankingStateUs::$table_connection, $datasetId ) )
            ->where ( "segment", "=", $column )
            ->where ( "dataset_id", "!=", 0 )
            ->where ( "dataset_id", "!=", '' )
            ->where ( "latest", "=", 1 )->first ();

        if ( !empty( $result ) ) {
            if ( $value !== "" && $value !== null ) {
                if ( floatval($value) < (floatval( $result->low_medium )) ) {
                    $data = ['text' => 'Low', 'color' => '#b6414a'];
                }
                if ( (floatval( $result->low_medium )) <= floatval($value) && floatval($value) < (floatval( $result->medium_high )) ) {
                    $data = ['text' => 'Medium', 'color' => '#fec97c'];
                }
                if ( floatval( $result->medium_high ) <= floatval($value) ) {
                    $data = ['text' => 'High', 'color' => '#2c8c7c'];
                }
            } else {
                $data = ['text' => 'N/A', 'color' => '#c7c8c9'];
            }
        }

        return $data;
    }


    public function getStateName()
    {
        $state = [];
        $objResult = DB::table ( "states" )->select ( "name", "code" )->where ( "country_id", 231 )->get ();
        if ( !empty( $objResult ) ) {

            foreach ( $objResult as $rows ) {

                $state[ $rows->code ] = $rows->name;
            }
        }

        return $state;

    }

    public function getInvestmentRankByDataSet($dataSetId = null)
    {
        $data = [];
        $data[ 'status' ] = [];
        $data[ 'data_set_id' ] = $dataSetId;
        $data[ 'rank_data' ] = [];

        $data[ 'rank_data' ] = $this->model
            ->select (
                [
                    "state", "legalization", "cultivation", "retail", "manufacturing", "distribution", "ancillary",
                    "risk", "opportunity", "description", "dataset_id",
                    "latest as status"
                ] )
            ->where ( "dataset_id", $dataSetId )
            ->get ();

        if ( count ( $data[ 'rank_data' ] ) > 0 ) {
            $data[ 'status' ] = $data[ 'rank_data' ][ 0 ]->status;
        }

        return $data;
    }


    public function getAllInvestmentRankdDetails($request = null)
    {
        $this->perPage = env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn = (!empty( $request[ 'id' ] )) ? ($request[ 'id' ]) : 'id';
        $data =  $this->model
            ->select (
                [
                   "id", "state", "legalization", "cultivation", "retail", "manufacturing", "distribution", "ancillary",
                    "risk", "opportunity", "description", "dataset_id",
                    "latest as status"
                ] )
            ->orderBy ( $this->sortColumn, $this->sort )
            ->paginate($this->perPage);
        return $data;
    }


}
