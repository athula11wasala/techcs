<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\Chart;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Support\Facades\Config;
use Excel;
use App\DasetFactory\DatasetFactoryMethod;
use App\Models\InvestmentRankingStateUs;
use App\Models\InvestmentRankingThresholdUs;
use App\Models\DataSet;

class InvestmentRankingThresholdRepository extends Repository
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
        return 'App\Models\InvestmentRankingThresholdUs';
    }


    public function getuploadExcelPath($data_array)
    {
        $data[ 'investment_ranking' ] = '';
        if ( !empty( $data_array[ 'investment_ranking' ] ) ) {

            if ( gettype ( $data_array[ 'investment_ranking' ] ) == 'object' ) {
                $destinationPath = ltrim ( Config::get ( 'custom_config.INVESTMENT_RANK_STORAGE' ), "/" );
                $image = $data_array[ 'investment_ranking' ];
                $fileName = rand () . "_" . 'investment_rank.' . $image->getClientOriginalExtension ();
                if ( !file_exists ( $destinationPath ) ) {
                    mkdir ( $destinationPath, 0777, true );
                }

                $data_array[ 'investment_ranking' ]->move ( $destinationPath, $fileName );
                $data[ 'investment_ranking' ] = $destinationPath . $fileName;
                $flag = true;
            }
        }

        return $data;
    }


    public function saveThreshold($data, $datasetId = null, $latest = null)
    {
        $objDatasetFac = new  DatasetFactoryMethod();
        $flag = false;
        $datasetId = $datasetId;
        $insert = [];

        if ( !empty( $data ) && $data->count () ) {

            foreach ( $data as $rows ) {
                if ( !empty( $rows[ 'segment' ] ) ) {
                    $tags = array_map ( function ($rows) use ($objDatasetFac, $datasetId, $latest) {
                        return $objDatasetFac->makeDatasetRead ( $rows, ['id' => 2, 'dataset_id' => $datasetId, 'latest' => $latest] );
                    }, (array)$rows );
                    $key = array_keys ( $tags );
                    $insert[] = $tags[ $key[ 1 ] ];
                }

            }
            if ( !empty( $insert ) ) {

                foreach ( $insert as $rows ) {

                    $objInvestmentThreshold = new   InvestmentRankingThresholdUs();
                    $objInvestmentThreshold->segment = $rows[ 'segment' ];
                    $objInvestmentThreshold->low_medium = $rows[ 'low_medium' ];
                    $objInvestmentThreshold->medium_high = $rows[ 'medium_high' ];
                    $objInvestmentThreshold->dataset_id = $rows[ 'dataset_id' ];
                    $objInvestmentThreshold->latest = $rows[ 'latest' ];
                    $objInvestmentThreshold->save ();
                    $flag = true;
                }

                return ['success' => $flag, "datasetId" => $datasetId];
            }
            return ['success' => $flag, "datasetId" => $datasetId];
        }

    }

    public function uploadExcel($request = null)
    {
        $excelFileName = "";
        if ( gettype ( $request[ 'investment_ranking' ] ) == 'object' ) {
            $excelFileName = $request[ 'investment_ranking' ]->getClientOriginalName ();
        }
        $uploadPath = $this->getuploadExcelPath ( $request )[ 'investment_ranking' ];
        $latest = (!empty( $request[ 'status' ] )) ? ($request[ 'status' ]) : 0;
        $flag = false;
        $data = ['success' => false, "message" => ""];

        if ( $uploadPath == null ) {
            $data[ 'message' ] = 'There were uploded wrong Investment Opportunity Ranking';
            return $data;
        }
        $path = base_path ( "public/" . $uploadPath );
        try {

            $data = Excel::selectSheetsByIndex ( 0, 1 )->load ( $path, function ($reader) {
            } )->get ();
        } catch (\PHPExcel_Exception $ex) {

            $data[ 'message' ] = 'There were uploded wrong Investment Opportunity Ranking';
            $this->deleteUploadFile ( !empty( $uploadPath ) ? $uploadPath : null );
            return $data;
        }

        $this->deleteUploadFile ( !empty( $uploadPath ) ? $uploadPath : null );

        $headerThresoldColumn = $data[ 1 ]->first ()->keys ()->toArray ();
        $headerRankingState = $data[ 0 ]->first ()->keys ()->toArray ();

        if ( isset( $headerThresoldColumn[ 0 ] ) && isset( $headerThresoldColumn[ 1 ] ) && isset( $headerThresoldColumn[ 2 ] ) &&
            $headerThresoldColumn[ 0 ] == "segment" && $headerThresoldColumn[ 1 ] == "low_medium" && $headerThresoldColumn[ 2 ] == "medium_high" ) {

            if ( isset( $headerRankingState[ 0 ] ) && isset( $headerRankingState[ 1 ] ) && isset( $headerRankingState[ 2 ] ) &&
                isset( $headerRankingState[ 3 ] ) && isset( $headerRankingState[ 4 ] ) && isset( $headerRankingState[ 5 ] ) &&
                isset( $headerRankingState[ 6 ] ) && isset( $headerRankingState[ 7 ] ) && isset( $headerRankingState[ 8 ] ) &&
                isset( $headerRankingState[ 9 ] ) && $headerRankingState[ 0 ] == "state" && $headerRankingState[ 1 ] == "legalization" &&
                $headerRankingState[ 2 ] == "cultivation" && $headerRankingState[ 3 ] == "retail" && $headerRankingState[ 4 ] == "manufacturing" &&
                $headerRankingState[ 5 ] == "distribution" && $headerRankingState[ 6 ] == "ancillary" && $headerRankingState[ 7 ] == "risk" &&
                $headerRankingState[ 8 ] == "opportunity" && $headerRankingState[ 9 ] == "description" ) {
                $flag = true;
            }
        }

        if ( $flag == false ) {
            $data[ 'message' ] = 'There were uploded wrong Investment Opportunity Ranking';
            return $data;
        }
        $dataSetId = $this->addDataset ( $excelFileName );

        if ( $this->saveThreshold ( $data[ 1 ], $dataSetId, $latest )[ 'success' ] == true ) {

            if ( $this->saveInvestmentRankState ( $data[ 0 ], $dataSetId, $latest ) [ 'success' ] == true ) {

                if ( $latest == 1 ) {

                    $this->inActiveInvestmentRankdDetails ( $dataSetId );
                }
                return ['success' => true, "message" => ""];
            }

        }
        return ['success' => false, "message" => "Error Occured"];

    }

    public function deleteThreshold($dataSet)
    {
        DB::Connection ( "mysql_external_intake" )->table ( "investment_ranking_state_us" )->where ( "dataset_id", $dataSet )->delete ();
        DB::Connection ( "mysql_external_intake" )->table ( "investment_ranking_threshold_us" )->where ( "dataset_id", $dataSet )->delete ();
    }

    public function updateThreshold($dataSet, $latest)
    {
       $this->inActiveInvestmentRankdDetails ( $dataSet );

        DB::Connection ( "mysql_external_intake" )->table ( "investment_ranking_state_us" )->where ( 'dataset_id', $dataSet )->update ( ['latest' => $latest] );
        DB::Connection ( "mysql_external_intake" )->table ( "investment_ranking_threshold_us" )->where ( 'dataset_id', $dataSet )->update ( ['latest' => $latest] );
        return true;
    }

    public function uploadEditExcel($request = null)
    {
        $uploadPath = $this->getuploadExcelPath ( $request )[ 'investment_ranking' ];
        $latest = (!empty( $request[ 'status' ] )) ? ($request[ 'status' ]) : 0;
        $flag = false;
        $data = ['success' => false, "message" => ""];

        if ( $uploadPath == null ) {
            $data[ 'message' ] = 'There were uploded wrong Investment Opportunity Ranking';
            return $data;

        }
        $path = base_path ( "public/" . $uploadPath );
        $data = Excel::selectSheetsByIndex ( 0, 1 )->load ( $path, function ($reader) {
        } )->get ();
        $this->deleteUploadFile ( !empty( $uploadPath ) ? $uploadPath : null );

        $headerThresoldColumn = $data[ 1 ]->first ()->keys ()->toArray ();
        $headerRankingState = $data[ 0 ]->first ()->keys ()->toArray ();

        if ( isset( $headerThresoldColumn[ 0 ] ) && isset( $headerThresoldColumn[ 1 ] ) && isset( $headerThresoldColumn[ 2 ] ) &&
            $headerThresoldColumn[ 0 ] == "segment" && $headerThresoldColumn[ 1 ] == "low_medium" && $headerThresoldColumn[ 2 ] == "medium_high" ) {

            if ( isset( $headerRankingState[ 0 ] ) && isset( $headerRankingState[ 1 ] ) && isset( $headerRankingState[ 2 ] ) &&
                isset( $headerRankingState[ 3 ] ) && isset( $headerRankingState[ 4 ] ) && isset( $headerRankingState[ 5 ] ) &&
                isset( $headerRankingState[ 6 ] ) && isset( $headerRankingState[ 7 ] ) && isset( $headerRankingState[ 8 ] ) &&
                isset( $headerRankingState[ 9 ] ) && $headerRankingState[ 0 ] == "state" && $headerRankingState[ 1 ] == "legalization" &&
                $headerRankingState[ 2 ] == "cultivation" && $headerRankingState[ 3 ] == "retail" && $headerRankingState[ 4 ] == "manufacturing" &&
                $headerRankingState[ 5 ] == "distribution" && $headerRankingState[ 6 ] == "ancillary" && $headerRankingState[ 7 ] == "risk" &&
                $headerRankingState[ 8 ] == "opportunity" && $headerRankingState[ 9 ] == "description" ) {
                $flag = true;
            }
        }

        if ( $flag == false ) {

            $data[ 'message' ] = 'There were uploded wrong Investment Opportunity Ranking';
            return $data;
        }


        $dataSetId = !empty( $request[ 'dataset_id' ] ) ? $request[ 'dataset_id' ] : 0;
        $this->deleteThreshold ( $dataSetId );

        if ( $this->saveThreshold ( $data[ 1 ], $dataSetId, $latest )[ 'success' ] == true ) {
            if ( $this->saveInvestmentRankState ( $data[ 0 ], $dataSetId, $latest ) [ 'success' ] == true ) {
                if ( $latest == 1 ) {
                    $this->inActiveInvestmentRankdDetails ( $dataSetId );
                }
                return ['success' => true, "message" => ""];
            }

        }
        return ['success' => false, "message" => "Error Occured"];

    }


    public function inActiveInvestmentRankdDetails($dataSetId = null)
    {
        if ( !empty( $dataSetId ) ) {
            $objThrshold = $this->model->where ( "dataset_id", "!=", $dataSetId )
                ->update ( ["latest" => 0] );

            $objInvestmentRank = DB::Connection ( "mysql_external_intake" )->table ( "investment_ranking_state_us" )
                ->where ( "dataset_id", "!=", $dataSetId )
                ->update ( ["latest" => 0] );
        }
    }

    public function editThreshold($data)
    {
        $dataset_id = !empty( $data[ 'dataset_id' ] ) ? $data[ 'dataset_id' ] : 0;
        $id = !empty( $data[ 'id' ] ) ? $data[ 'id' ] : 0;
        $low_medium = !empty( $data[ 'low_medium' ] ) ? $data[ 'low_medium' ] : 0;
        $medium_high = !empty( $data[ 'medium_high' ] ) ? $data[ 'medium_high' ] : 0;
        $objThreshold = InvestmentRankingThresholdUs::where ( "dataset_id", $dataset_id )->where ( "id", "=", $id )->first ();

        if ( !empty( $objThreshold ) ) {
            if ( !empty( $low_medium ) ) {
                $objThreshold->low_medium = $low_medium;
            }
            if ( !empty( $medium_high ) ) {
                $objThreshold->medium_high = $medium_high;
            }

            try {
                if ( $objThreshold->save () ) {
                    return true;
                }
            } catch (\Illuminate\Database\QueryException $ex) {

                return false;
            }


        }

        /*if ( DB::Connection ( "mysql_external_intake" )->table ( "investment_ranking_threshold_us" )
            ->where ( "dataset_id", "=", $dataset_id )
            ->where ( "id", "=", $id )
            ->update ( ["low_medium" => $low_medium, "medium_high" => $medium_high] ) ) {
            return true;
        }*/
        return false;
    }

    public function saveInvestmentRankState($data, $datasetId = null, $latest = null)
    {
        $objDatasetFac = new  DatasetFactoryMethod();
        $flag = false;
        $insert = [];
        if ( !empty( $data ) && $data->count () ) {

            foreach ( $data as $rows ) {
        \Log::info("==== saveInvestmentRankState->distribution ", ['u' => json_encode($rows)]);
                if ( !empty( $rows[ 'state' ] ) ) {
                    $tags = array_map ( function ($rows) use ($objDatasetFac, $datasetId, $latest) {
                        return $objDatasetFac->makeDatasetRead ( $rows, ['id' => 3, 'dataset_id' => $datasetId, 'latest' => $latest] );
                    }, (array)$rows );
        \Log::info("==== saveInvestmentRankState->tags ", ['u' => json_encode($tags)]);
                    $key = array_keys ( $tags );
                    $insert[] = $tags[ $key[ 1 ] ];

                }
            }
            if ( !empty( $insert ) ) {

                foreach ( $insert as $rows ) {
                    $objInvestmentThresholdState = new   InvestmentRankingStateUs();
                    $objInvestmentThresholdState->state = $rows[ 'state' ];
                    $objInvestmentThresholdState->legalization = $rows[ 'legalization' ];
                    $objInvestmentThresholdState->cultivation = $rows[ 'cultivation' ];
                    $objInvestmentThresholdState->retail = $rows[ 'retail' ];
                    $objInvestmentThresholdState->manufacturing = $rows[ 'manufacturing' ];
        \Log::info("==== saveInvestmentRankState->manufacturing ", ['u' => json_encode($objInvestmentThresholdState->manufacturing)]);
                    $objInvestmentThresholdState->distribution = $rows[ 'distribution' ];
                    $objInvestmentThresholdState->ancillary = $rows[ 'ancillary' ];
                    $objInvestmentThresholdState->risk = $rows[ 'risk' ];
                    $objInvestmentThresholdState->opportunity = $rows[ 'opportunity' ];
                    $objInvestmentThresholdState->description = $rows[ 'description' ];
                    $objInvestmentThresholdState->dataset_id = $rows[ 'dataset_id' ];
                    $objInvestmentThresholdState->latest = $rows[ 'latest' ];
                    $objInvestmentThresholdState->save ();
                    $flag = true;
                }

                return ['success' => $flag, "datasetId" => $datasetId];
            }
            return ['success' => $flag, "datasetId" => $datasetId];
        }
    }


    public function addDataset($fileName)
    {
        $fromDate = date ( "Y-m-d" );
        $toDate = date ( 'Y-m-d', strtotime ( "+3 months", strtotime ( $fromDate ) ) );
        // $description = "rankingdata-" . $fromDate . ".csv";
        $description = $fileName;
        $objDataSet = new DataSet();
        $objDataSet->data_set = Config::get ( "custom_config.data_set.investment_ranking_threshold_us" );
        $objDataSet->from = $fromDate;
        $objDataSet->to = $toDate;
        $objDataSet->description = $description;
        $objDataSet->save ();

        return !empty( $objDataSet->id ) ? $objDataSet->id : 0;

    }



    public function deleteUploadFile($excelFile = null)
    {

        if ( !empty( $excelFile ) ) {

            \File::delete ( public_path ( "/" . $excelFile )
            );
        }

    }

    public function getAllThresholdDetails($request)
    {
        //  $this->perPage = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : env ( 'PAGINATE_PER_PAGE', 15 );
        //  $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        //  $this->sortColumn = (!empty( $request[ 'id' ] )) ? ($request[ 'id' ]) : 'id';
        return $this->model
            ->select (
                [
                    "id", "segment", "low_medium", "medium_high", "dataset_id", "latest as status"
                ] )
            ->where ( "latest", 1 )
            //->orderBy ( $this->sortColumn, $this->sort )
            ->get ();
    }


}

