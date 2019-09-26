<?php

namespace App\Repositories;

use App\Models\DataSet;
use App\Models\StateLegalized;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;
use App\Equio\Helper;

class StateLegalizedRepository extends Repository
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
        return 'App\Models\StateLegalized';
    }

    public function getAllState()
    {

        return $this->model
            ->select (
                [
                    "state_ABV"
                ]
            )
            ->where ( 'state', '!=', '' )
            ->orderBy ( "id", "asc" )
            ->get ();
    }


    public function getStateLegalziedInfo()
    {

        $objHelper = new Helper();
        $datasetId = Config::get ( 'custom_config.data_set' )[ 'LegalizedStates' ];
        $this->perPage = env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'asc';
        $this->sortColumn = (!empty( $request[ 'id' ] )) ? ($request[ 'id' ]) : 'state';
        $allState = $this->getAllState ();
        $data = [];
        $stateNameArr = [];

        $i = 0;

        $result = $this->model
            ->select (
                [
                    "*"
                ] )
            ->where ( "dataset_id", $objHelper->getDataSetId ( StateLegalized::$table_connection, $datasetId ) )
            ->where ( "dataset_id", "!=", 0 )
            ->where ( "dataset_id", "!=", '' )
            ->where ( "latest", "=", 1 )
            ->orderBy ( $this->sortColumn, $this->sort )
            ->get ();

        foreach ( $result as $rows ) {

            foreach ( $allState as $stateName ) {

                if ( $stateName->state_ABV == $rows->state_ABV ) {

                    $data[ $rows->state_ABV ] = $rows;
                }

            }

        }

        return $data;


    }


    public function getStateLegalziedDetails($type)
    {
        $objHelper = new Helper();
        $datasetId = Config::get ( 'custom_config.data_set' )[ 'LegalizedStates' ];

        $allState = $this->getAllState ();
        $data = [];
        $i = 0;

        $result = $this->model
            ->select (
                [
                    "*",
                    DB::raw (
                        'if(
                            medical_legalization != 0 && recreational_legalized != 0,2,
                            if(medical_legalization = 0 && recreational_legalized = 0,3,1)
                            ) as type'
                    )
                ] )
            ->where ( "dataset_id", $objHelper->getDataSetId ( StateLegalized::$table_connection, $datasetId ) )
            ->where ( "dataset_id", "!=", 0 )
            ->where ( "dataset_id", "!=", '' )
            ->where ( "latest", "=", 1 );

        if ( !empty( $type ) ) {
            $result = $result->havingRaw ( 'type = ' . $type );
        }

        $result = $result->get ();

        foreach ( $result as $rows ) {

            foreach ( $allState as $stateName ) {

                if ( $stateName->state_ABV == $rows->state_ABV ) {

                    $data[ $rows->state_ABV ][ 'state' ] = $rows->state;
                    $data[ $rows->state_ABV ][ 'medicalLegalization' ] = $rows->medical_legalization;
                    $data[ $rows->state_ABV ][ 'recreationalLegalized' ] = $rows->recreational_legalized;
                    $data[ $rows->state_ABV ][ 'decriminalized' ] = $rows->decriminalized;
                    $data[ $rows->state_ABV ][ 'lowThccbdRatioLaw' ] = $rows->low_thccbd_ratio_law;
                    $data[ $rows->state_ABV ][ 'medicalCultivation' ] = $rows->medical_cultivation;
                    $data[ $rows->state_ABV ][ 'reciprocity' ] = $rows->reciprocity;
                    $data[ $rows->state_ABV ][ 'medicalDispensariesNumber' ] = $rows->medical_dispensaries_number;
                    $data[ $rows->state_ABV ][ 'retailOperations' ] = $rows->retail_operations;

                    $data[ $rows->state_ABV ][ 'adultUseCultivation' ] = $rows->adult_use_cultivation;
                    $data[ $rows->state_ABV ][ 'medical' ] = $rows->medical;
                    $data[ $rows->state_ABV ][ 'recreational' ] = $rows->recreational;

                    $quarter = substr ( $rows->quarter, 0, 4 );
                    $data[ $rows->state_ABV ][ 'medicalSales' ] = $quarter . " Medical Sales : " . $rows->medical_sales;
                    $data[ $rows->state_ABV ][ 'adultSales' ] = $quarter . " Adult Use Sales : " . $rows->adult_sales;
                    $data[ $rows->state_ABV ][ 'type' ] = $rows->type;

                }

            }

        }
        return $data;

    }

}
