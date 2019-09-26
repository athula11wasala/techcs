<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\DataSet;
use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Config;

class CannabisBenchmarksUsRepository extends Repository
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
        return 'App\Models\CannabisBenchmarksUs';
    }

    public function getLatestDatasetIdCallBack($datasetId, $tblId)
    {
        $dataset = DataSet::where ( "data_set", $datasetId )
            ->select ( "id" )
            ->where ( "id", $tblId )
            ->get ();
        if ( count ( $dataset ) == 1 ) {
            $id = !empty( $dataset[ 0 ]->id ) ? $dataset[ 0 ]->id : '';
        } elseif ( count ( $dataset ) == 0 ) {
            $id = !empty( $dataset[ 0 ]->id ) ? $dataset[ 0 ]->id : '';
        }
        if ( !empty( $id ) ) {
            $resultCount = $this->model
                ->where ( "dataset_id", $id )
                ->where ( "dataset_id", "!=", 0 )
                ->where ( "dataset_id", "!=", '' )
                ->where ( "latest", "=", 1 )->count ();
            if ( $resultCount > 0 ) {
                $id = !empty( $dataset[ 0 ]->id ) ? $dataset[ 0 ]->id : '';
                return $id;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getLatestDatasetId()
    {
        $tblId = [];
        $datasetId = Config::get ( 'custom_config.data_set' )[ 'CannabisBenchmarksUs' ];
        $dataset = DataSet::where ( "data_set", $datasetId )
            ->select ( "id" )
            ->orderBy ( "to", 'desc' )
            ->get ();


        foreach ( $dataset as $rows ) {

            $tblId[] = $rows->id;
        }

        if ( count ( $dataset ) == 1 ) {

            return !empty( $dataset[ 0 ]->id ) ? $dataset[ 0 ]->id : '';
        } elseif ( count ( $dataset ) == 0 ) {
            return !empty( $dataset[ 0 ]->id ) ? $dataset[ 0 ]->id : '';
        } elseif ( count ( $dataset ) > 1 ) {

            $result = $this->model
                ->where ( "dataset_id", $dataset[ 0 ]->id )
                ->where ( "dataset_id", "!=", 0 )
                ->where ( "dataset_id", "!=", '' )
                ->where ( "latest", "=", 1 )->count ();

            if ( $result > 0 ) {

                $id = !empty( $dataset[ 0 ]->id ) ? $dataset[ 0 ]->id : '';
                return $id;

            }

            $maxId = DataSet::where ( "data_set", $datasetId )
                ->select ( "id" )
                ->where ( "id", "!=", $dataset[ 0 ]->id )
                ->orderBy ( "to", 'desc' )
                ->get ();

            $id = !empty( $maxId[ 0 ]->id ) ? $maxId[ 0 ]->id : '';

            $resultCount = $this->model
                ->where ( "dataset_id", $id )
                ->where ( "dataset_id", "!=", 0 )
                ->where ( "dataset_id", "!=", '' )
                ->where ( "latest", "=", 1 )->count ();

            if ( $resultCount > 0 ) {

                return !empty( $maxId[ 0 ]->id ) ? $maxId[ 0 ]->id : '';

            } else if ( $resultCount == 0 ) {

                unset( $tblId[ 0 ] );
                $num = 0;
                foreach ( $tblId as $result ) {
                    $num++;

                    if ( $num > 1 ) {
                        $id = $this->getLatestDatasetIdCallBack ( $datasetId, $result );
                        if ( !empty( $id ) ) {

                            return $id;

                        }
                    }

                }

            }

        }
        return '';

    }

    /**
     * Returns basic graph information
     * @return mixed
     */
    public function showGraphInfo($request)
    {

        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn = (!empty( $request[ 'orderid' ] )) ? ($request[ 'orderid' ]) : 'week_ending';
        $this->limit = (!empty( $request[ 'limit' ] )) ? ($request[ 'limit' ]) : 8;

        $data = [];

        $db_ext = \DB::connection ( 'mysql_cms' );
        $result = $db_ext->table ( 'cannabis_benchmarks_us' )
            ->select (
                [
                    'week_ending', 'spot', 'indoor', 'greenhouse', 'outdoor', 'medical', 'adult_use'

                ] )
            /* ->where ( "dataset_id", $this->getLatestDatasetId () )
             ->where ( "dataset_id", "!=", 0 )
             ->where ( "dataset_id", "!=", '' )
             ->where ( "latest", "=", 1 ) */
            ->orderBy ( $this->sortColumn, $this->sort )
            ->limit ( $this->limit )
            ->get ();

        $i = 0;

        if ( count ( $result ) == 0 ) {
            return $data;
        }

        foreach ( $result as $value ) {

            $i++;

            $data[ 'type' ][ $i ] =
                !empty( $value->week_ending ) ? date ( 'm-d-y', strtotime ($value->week_ending) ) : '';

            $data[ 'prices' ][ 0 ][ $i ] =
                !empty( $value->spot ) ? ($value->spot) : '';

            $data[ 'prices' ][ 1 ][ $i ] =
                !empty( $value->indoor ) ? ($value->indoor) : '';

            $data[ 'prices' ][ 2 ][ $i ] =
                !empty( $value->greenhouse ) ? ($value->greenhouse) : '';

            $data[ 'prices' ][ 3 ][ $i ] =
                !empty( $value->outdoor ) ? ($value->outdoor) : '';

            $data[ 'prices' ][ 4 ][ $i ] =
                !empty( $value->medical ) ? ($value->medical) : '';

            $data[ 'prices' ][ 5 ][ $i ] =
                !empty( $value->adult_use ) ? ($value->adult_use) : '';

            if ( $i == 1 ) {
                $date = !empty( $value->week_ending ) ? ($value->week_ending) : '';
                $date = date ( 'F d, Y', strtotime ( $date ) );
                $data[ 'endDate' ] = $date;
            }

        }

        $data[ 'type' ] = !empty( $data[ 'type' ] ) ? (object)$data[ 'type' ] : '';
        $data[ 'prices' ] = !empty( $data[ 'prices' ] ) ? (object)$data[ 'prices' ] : '';

        return $data;

    }

    public function showPriceInfoV1($request)
    {

        $this->perPage = env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn = (!empty( $request[ 'orderid' ] )) ? ($request[ 'orderid' ]) : 'week_ending';
        $this->limit = (!empty( $request[ 'limit' ] )) ? ($request[ 'limit' ]) : 2;

        $result = $this->model
            ->select (
                [
                    'id', 'week_ending', 'spot', 'indoor', 'greenhouse', 'outdoor', 'medical', 'adult_use'

                ] )
            /* ->where ( "dataset_id", $this->getLatestDatasetId () )
             ->where ( "dataset_id", "!=", 0 )
             ->where ( "dataset_id", "!=", '' )
             ->where ( "latest", "=", 1 ) */
            ->orderBy ( $this->sortColumn, $this->sort )
            ->limit ( $this->limit )
            ->get ();

        $data = [];
        $endDate = null;
        $i = null;

        foreach ( $result as $rows ) {
            $i++;

            $rows->spot = Helper::stringReplace ( $rows->spot );
            $rows->indoor = Helper::stringReplace ( $rows->indoor );
            $rows->greenhouse = Helper::stringReplace ( $rows->greenhouse );
            $rows->outdoor = Helper::stringReplace ( $rows->outdoor );
            $rows->medical = Helper::stringReplace ( $rows->medical );
            $rows->adult_use = Helper::stringReplace ( $rows->adult_use );

            if ( $i == 1 ) {

                $changepostprice_old = (int)($rows->spot);
                $changeindoorPrice_old = (int)($rows->indoor);
                $changegreenhousePrice_old = (int)($rows->greenhouse);
                $changeoutdoorPrice_old = (int)($rows->outdoor);
                $changemedicalPrice_old = (int)($rows->medical);
                $changeadult_usePrice_old = (int)($rows->adult_use);
                $endDate = $rows->week_ending;;

            } else {

                $spotPercentage = $this->percetnageCalculation ( (int)($rows->spot), $changepostprice_old );
                $spotGrowth = $changepostprice_old > $rows->spot ? true : false;

                $data[] = [
                    'name' => 'US Spot Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . $changepostprice_old,
                    'startValue' => "$" . $rows->spot,
                    'changePrice' => (int)($changepostprice_old) - (int)($rows->spot),
                    'changePrecent' => ($spotGrowth == false) ? "-" . $spotPercentage : $spotPercentage,
                    'growth' => $spotGrowth

                ];

                $indoorPercentage = $this->percetnageCalculation ( (int)($rows->indoor), $changeindoorPrice_old );
                $indoorGrowth = $changeindoorPrice_old > $rows->indoor ? true : false;

                $data[] = [
                    'name' => 'US Indoor Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . $changeindoorPrice_old,
                    'startValue' => "$" . $rows->indoor,
                    'changePrice' => (int)$changeindoorPrice_old - (int)($rows->indoor),
                    'changePrecent' => ($indoorGrowth == false) ? "-" . $indoorPercentage : $indoorPercentage,
                    'growth' => $indoorGrowth

                ];

                $greenHousePercentage = $this->percetnageCalculation ( (int)($rows->greenhouse),
                    $changegreenhousePrice_old );
                $greenHouseGrowth = $changegreenhousePrice_old > $rows->greenhouse ? true : false;

                $data[] = [
                    'name' => 'US Greenhouse Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . $changegreenhousePrice_old,
                    'startValue' => "$" . $rows->greenhouse,
                    'changePrice' => (int)$changegreenhousePrice_old - (int)($rows->greenhouse),
                    'changePrecent' => ($greenHouseGrowth == false) ? "-" . $greenHousePercentage :
                        $greenHousePercentage,
                    'growth' => $greenHouseGrowth

                ];

                $outPercentage = $this->percetnageCalculation ( (int)($rows->outdoor), $changeoutdoorPrice_old );
                $outdoorGrowth = $changeoutdoorPrice_old > $rows->outdoor ? true : false;


                $data[] = [
                    'name' => 'US Outdoor Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . $changeoutdoorPrice_old,
                    'startValue' => "$" . $rows->outdoor,
                    'changePrice' => (int)$changeoutdoorPrice_old - (int)($rows->outdoor),
                    'changePrecent' => (($outdoorGrowth) == false) ? "-" . $outPercentage : $outPercentage,
                    'growth' => $outdoorGrowth

                ];

                $medicalPercentage = $this->percetnageCalculation ( (int)($rows->medical), $changemedicalPrice_old );
                $medicalGrowth = $changemedicalPrice_old > $rows->medical ? true : false;

                $data[] = [
                    'name' => 'US Medical Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . $changemedicalPrice_old,
                    'startValue' => "$" . $rows->medical,
                    'changePrice' => (int)$changemedicalPrice_old - (int)($rows->medical),
                    'changePrecent' => (($medicalGrowth) == false) ? "-" . $medicalPercentage : $medicalPercentage,
                    'growth' => $medicalGrowth

                ];

                $adultPercentage = $this->percetnageCalculation ( (int)($rows->adult_use), $changeadult_usePrice_old );
                $adultGrowth = $changeadult_usePrice_old > $rows->adult_use ? true : false;

                $data[] = [
                    'name' => 'US Adult Use Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . $changeadult_usePrice_old,
                    'startValue' => "$" . $rows->adult_use,
                    'changePrice' => (int)$changeadult_usePrice_old - (int)($rows->adult_use),
                    'changePrecent' => ($adultGrowth == false) ? "-" . $adultPercentage : $adultPercentage,
                    'growth' => $adultGrowth

                ];

            }

        }
        return $data;

    }

    public function showPriceInfo($request)
    {

        $this->perPage = env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn = (!empty( $request[ 'orderid' ] )) ? ($request[ 'orderid' ]) : 'week_ending';
        $this->limit = (!empty( $request[ 'limit' ] )) ? ($request[ 'limit' ]) : 2;
        $db_ext = \DB::connection ( 'mysql_cms' );
        $result = $db_ext->table ( 'cannabis_benchmarks_us' )
            ->select (
                [
                    'id', 'week_ending', 'spot', 'indoor', 'greenhouse', 'outdoor', 'medical', 'adult_use'
                ] )
            ->orderBy ( $this->sortColumn, $this->sort )
            ->limit ( $this->limit )
            ->get ();

        $data = [];
        $endDate = null;
        $i = null;

        foreach ( $result as $rows ) {
            $i++;

            $rows->spot = Helper::stringReplace ( $rows->spot );
            $rows->indoor = Helper::stringReplace ( $rows->indoor );
            $rows->greenhouse = Helper::stringReplace ( $rows->greenhouse );
            $rows->outdoor = Helper::stringReplace ( $rows->outdoor );
            $rows->medical = Helper::stringReplace ( $rows->medical );
            $rows->adult_use = Helper::stringReplace ( $rows->adult_use );

            if ( $i == 1 ) {

                $changepostprice_old = (int)($rows->spot);
                $changeindoorPrice_old = (int)($rows->indoor);
                $changegreenhousePrice_old = (int)($rows->greenhouse);
                $changeoutdoorPrice_old = (int)($rows->outdoor);
                $changemedicalPrice_old = (int)($rows->medical);
                $changeadult_usePrice_old = (int)($rows->adult_use);
                $endDate = $rows->week_ending;;

            } else {

                $spotPercentage = $this->percetnageCalculation ( (int)($rows->spot), $changepostprice_old );
                $spotGrowth = $changepostprice_old > $rows->spot ? true : false;

                $data[] = [
                    'name' => 'US Spot Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . number_format ( $changepostprice_old ),
                    //  'endValue' => "$" .  Helper::number_format_short( $changepostprice_old, 2),
                    'startValue' => "$" . number_format ( $rows->spot ),
                    'changePrice' => (int)($changepostprice_old) - (int)($rows->spot),
                    'changePrecent' => ($spotGrowth == false) ? "-" . $spotPercentage : $spotPercentage,
                    'growth' => $spotGrowth

                ];

                $indoorPercentage = $this->percetnageCalculation ( (int)($rows->indoor), $changeindoorPrice_old );
                $indoorGrowth = $changeindoorPrice_old > $rows->indoor ? true : false;

                $data[] = [
                    'name' => 'US Indoor Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . number_format ( $changeindoorPrice_old ),
                    'startValue' => "$" . number_format ( $rows->indoor ),
                    'changePrice' => (int)$changeindoorPrice_old - (int)($rows->indoor),
                    'changePrecent' => ($indoorGrowth == false) ? "-" . $indoorPercentage : $indoorPercentage,
                    'growth' => $indoorGrowth

                ];

                $greenHousePercentage = $this->percetnageCalculation ( (int)($rows->greenhouse),
                    $changegreenhousePrice_old );
                $greenHouseGrowth = $changegreenhousePrice_old > $rows->greenhouse ? true : false;

                $data[] = [
                    'name' => 'US Greenhouse Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . number_format ( $changegreenhousePrice_old ),
                    'startValue' => "$" . number_format ( $rows->greenhouse ),
                    'changePrice' => (int)$changegreenhousePrice_old - (int)($rows->greenhouse),
                    'changePrecent' => ($greenHouseGrowth == false) ? "-" . $greenHousePercentage :
                        $greenHousePercentage,
                    'growth' => $greenHouseGrowth

                ];

                $outPercentage = $this->percetnageCalculation ( (int)($rows->outdoor), $changeoutdoorPrice_old );
                $outdoorGrowth = $changeoutdoorPrice_old > $rows->outdoor ? true : false;


                $data[] = [
                    'name' => 'US Outdoor Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . number_format ( $changeoutdoorPrice_old ),
                    'startValue' => "$" . number_format ( $rows->outdoor ),
                    'changePrice' => (int)$changeoutdoorPrice_old - (int)($rows->outdoor),
                    'changePrecent' => (($outdoorGrowth) == false) ? "-" . $outPercentage : $outPercentage,
                    'growth' => $outdoorGrowth

                ];

                $medicalPercentage = $this->percetnageCalculation ( (int)($rows->medical), $changemedicalPrice_old );
                $medicalGrowth = $changemedicalPrice_old > $rows->medical ? true : false;

                $data[] = [
                    'name' => 'US Medical Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . number_format ( $changemedicalPrice_old ),
                    'startValue' => "$" . number_format ( $rows->medical ),
                    'changePrice' => (int)$changemedicalPrice_old - (int)($rows->medical),
                    'changePrecent' => (($medicalGrowth) == false) ? "-" . $medicalPercentage : $medicalPercentage,
                    'growth' => $medicalGrowth

                ];

                $adultPercentage = $this->percetnageCalculation ( (int)($rows->adult_use), $changeadult_usePrice_old );
                $adultGrowth = $changeadult_usePrice_old > $rows->adult_use ? true : false;

                $data[] = [
                    'name' => 'US Adult Use Index',
                    'endDate' => $endDate,
                    'startDate' => $rows->week_ending,
                    'endValue' => "$" . number_format ( $changeadult_usePrice_old ),
                    'startValue' => "$" . number_format ( $rows->adult_use ),
                    'changePrice' => (int)$changeadult_usePrice_old - (int)($rows->adult_use),
                    'changePrecent' => ($adultGrowth == false) ? "-" . $adultPercentage : $adultPercentage,
                    'growth' => $adultGrowth

                ];

            }

        }
        return $data;

    }

    public function percetnageCalculation($previousVal, $newVal)
    {
        $decrease = $previousVal - $newVal;
        $percentage = $decrease / $previousVal * 100;
        $percentage = number_format ( (float)$percentage, 1, '.', '' );

        return Helper::stringReplace ( $percentage );
    }


}