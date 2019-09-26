<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\Cannibalization;
use App\Models\DataSet;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;

class CannibalizationRepository extends Repository
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
        return 'App\Models\Cannibalization';
    }


    public function showAllState()
    {
        $this->perPage = env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->sortColumn = (!empty( $request[ 'id' ] )) ? ($request[ 'id' ]) : 'state';

        return $this->model
            ->select (
                [
                    DB::raw ( '(state_ABV)' )
                ] )
            ->orderBy ( $this->sortColumn, $this->sort )
            ->get ();
    }


    public function showDetailsByQuater($quater)
    {

        $this->perPage = env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'asc';
        $this->sortColumn = (!empty( $request[ 'id' ] )) ? ($request[ 'id' ]) : 'state';
        $allStates = $this->showAllState ();

        $result = $this->model
            ->select (
                [
                    "*", DB::raw ( 'EXTRACT(YEAR FROM date)as year' )
                ] )
            ->whereIn ( "quarter", $quater )
            ->orderBy ( $this->sortColumn, $this->sort )
            ->get ();
        return $result;
    }

    public function showDetailsByState()
    {

        $objHelper = new Helper();
        $datasetId = Config::get ( 'custom_config.data_set' )[ 'cannibalization' ];
        $this->perPage = env ( 'PAGINATE_PER_PAGE', 15 );
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'asc';
        $this->sortColumn = (!empty( $request[ 'id' ] )) ? ($request[ 'id' ]) : 'state';
        $allStates = $this->showAllState ();
        $calculationByQuarter = true;

        $result = $this->model
            ->select (
                [

                    "*", DB::raw ( 'EXTRACT(YEAR FROM date)as year' )
                ] )
            ->where ( "dataset_id", $objHelper->getDataSetId ( Cannibalization::$table_connection, $datasetId ) )
            ->where ( "dataset_id", "!=", 0 )
           ->where ( "dataset_id", "!=", '' )->where ( "latest", "=", 1 )
            ->orderBy ( $this->sortColumn, $this->sort )
            ->get ();

        $data = [];
        $data[ 'current_quater' ] = [];
        $i = null;
        $calc = 0;
        $a = array();
        $allQuater = [];
        if ( isset( $result )  && count ( $result)>0   ) {

            $quater = $result[ 0 ]->quarter;
            $year = mb_substr ( $quater, 3, 4 );

            if ( substr ( $quater, 0, 2 ) == 'Q1' ) {

                array_push ( $allQuater, 'Q1 ' . $year );
                $result = $this->showDetailsByQuater ( $allQuater );
                $calculationByQuarter = false;
                $data[ 'current_quater' ] = $quater;

            }

            if ( substr ( $quater, 0, 2 ) == 'Q2' ) {

                array_push ( $allQuater, 'Q1 ' . $year );
                array_push ( $allQuater, 'Q2 ' . $year );
                $result = $this->showDetailsByQuater ( $allQuater );
                $data[ 'current_quater' ] = $quater;
                $calculationByQuarter = false;

            }
            if ( substr ( $quater, 0, 2 ) == 'Q3' ) {
                array_push ( $allQuater, 'Q1 ' . $year );
                array_push ( $allQuater, 'Q2 ' . $year );
                array_push ( $allQuater, 'Q3 ' . $year );
                $result = $this->showDetailsByQuater ( $allQuater );
                $data[ 'current_quater' ] = $quater;
                $calculationByQuarter = false;
            }

            if ( substr ( $quater, 0, 2 ) == 'Q4' ) {
                array_push ( $allQuater, 'Q1 ' . $year );
                array_push ( $allQuater, 'Q2 ' . $year );
                array_push ( $allQuater, 'Q3 ' . $year );
                array_push ( $allQuater, 'Q4 ' . $year );
                $result = $this->showDetailsByQuater ( $allQuater );
                $data[ 'current_quater' ] = $quater;
                $calculationByQuarter = false;
            }

        }
        $ytdAlcholsales = 0;
        $canbblizationAlcholsales = 0;
        $cigarettesales = 0;
        $cannibalized_cigarettes_sales = 0;
        $non_cigarettesales = 0;
        $cannibalized_non_cigarettesales = 0;
        $tobacco_sales = 0;
        $cannibalized_tobacco_sales = 0;
        $pharma_sales = 0;
        $cannibalized_pharma_sales = 0;
        $cigarettesales_ = 0;
        $cannibalized_cigarettes_sales_ = 0;

        $resultCount = count ( $result );
        $i = 0;

        foreach ( $result as $rows ) {
            $i++;

            $quaterString = substr ( $rows->quarter, 0, 2 );

            if ( isset( $data[ 'state' ][ $rows->state_ABV ][ 'alcohol' ] [ 'ytd_data' ] [ 'alcohol' ] ) ) {
                $ytdAlcholsales = $ytdAlcholsales + intval ( !empty( $rows->alcohol ) ? $rows->alcohol : 0 );
                $canbblizationAlcholsales = $canbblizationAlcholsales + intval (
                        !empty( $rows->cannibalized_alcohol ) ? $rows->cannibalized_alcohol : 0 );
            } else {
                $ytdAlcholsales = intval ( !empty( $rows->alcohol ) ? $rows->alcohol : 0 );
                $canbblizationAlcholsales = intval (
                    !empty( $rows->cannibalized_alcohol ) ? $rows->cannibalized_alcohol : 0 );
            }
            if ( isset( $data[ 'state' ][ $rows->state_ABV ][ 'tobacco' ] [ 'ytd_data' ] [ 'cigarettes' ] ) ) {
                $cigarettesales = $cigarettesales + intval ( !empty( $rows->cigarettes ) ? $rows->cigarettes : 0 );
                $cannibalized_cigarettes_sales = $cannibalized_cigarettes_sales + intval (
                        !empty( $rows->cannibalized_cigarettes ) ? $rows->cannibalized_cigarettes : 0 );
            } else {
                $cigarettesales = intval ( !empty( $rows->cigarettes ) ? $rows->cigarettes : 0 );
                $cannibalized_cigarettes_sales = intval (
                    !empty( $rows->cannibalized_cigarettes ) ? $rows->cannibalized_cigarettes : 0 );
            }

            if ( isset( $data[ 'state' ][ $rows->state_ABV ][ 'tobacco' ] [ 'ytd_data' ] [ 'cigarettes' ] ) ) {
                $cigarettesales_ = $cigarettesales_ + intval ( !empty( $rows->cigarettes ) ? $rows->cigarettes : 0 );
                $cannibalized_cigarettes_sales_ = $cannibalized_cigarettes_sales_ + intval (
                        !empty( $rows->cannibalized_cigarettes ) ? $rows->cannibalized_cigarettes : 0 );
            } else {
                $cigarettesales_ = intval ( !empty( $rows->cigarettes ) ? $rows->cigarettes : 0 );
                $cannibalized_cigarettes_sales_ = intval (
                    !empty( $rows->cannibalized_cigarettes ) ? $rows->cannibalized_cigarettes : 0 );
            }
            if ( isset( $data[ 'state' ][ $rows->state_ABV ][ 'tobacco' ] [ 'ytd_data' ] [ 'non_cigarettes' ] ) ) {
                $non_cigarettesales = $non_cigarettesales + intval (
                        !empty( $rows->non_cigarettes ) ? $rows->non_cigarettes : 0 );
                $cannibalized_non_cigarettesales = $cannibalized_non_cigarettesales + intval (
                        !empty( $rows->cannibalized_non_cigarettes ) ? $rows->cannibalized_non_cigarettes : 0 );
            } else {
                $non_cigarettesales = intval ( !empty( $rows->non_cigarettes ) ? $rows->non_cigarettes : 0 );
                $cannibalized_non_cigarettesales = intval (
                    !empty( $rows->cannibalized_non_cigarettes ) ? $rows->cannibalized_non_cigarettes : 0 );
            }
            if ( isset( $data[ 'state' ][ $rows->state_ABV ][ 'tobacco' ] [ 'ytd_data' ] [ 'tobacco' ] ) ) {
                $tobacco_sales = $tobacco_sales + intval ( !empty( $rows->tobacco ) ? $rows->tobacco : 0 );
                $cannibalized_tobacco_sales = $cannibalized_tobacco_sales + intval (
                        !empty( $rows->cannibalized_tobacco ) ? $rows->cannibalized_tobacco : 0 );
            } else {
                $tobacco_sales = intval ( !empty( $rows->tobacco ) ? $rows->tobacco : 0 );
                $cannibalized_tobacco_sales = intval (
                    !empty( $rows->cannibalized_tobacco ) ? $rows->cannibalized_tobacco : 0 );
            }

            if ( isset( $data[ 'state' ][ $rows->state_ABV ][ 'pharma' ] [ 'ytd_data' ] [ 'pharma' ] ) ) {
                $pharma_sales = $pharma_sales + intval ( !empty( $rows->pharma ) ? $rows->pharma : 0 );
                $cannibalized_pharma_sales = $cannibalized_pharma_sales + intval (
                        !empty( $rows->cannibalized_pharma ) ? $rows->cannibalized_pharma : 0 );
            } else {
                $pharma_sales = intval ( !empty( $rows->pharma ) ? $rows->pharma : 0 );
                $cannibalized_pharma_sales = intval (
                    !empty( $rows->cannibalized_pharma ) ? $rows->cannibalized_pharma : 0 );
            }

            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'ytd_data' ][ 'cigarettes' ] =
                [
                    $rows->year . ' YTD Cigarettes Sales: $' . Helper::numberFormatShort (
                        $cigarettesales_,
                        2
                    ),
                    $rows->year . ' YTD Cannibalized Cigarettes Sales: $' . Helper::numberFormatShort (
                        $cannibalized_cigarettes_sales_,
                        2
                    ),
                    $rows->year . ' YTD Cannibalization: ' . $this->percentageCalculation (
                        (!empty( $cannibalized_cigarettes_sales_ ) ? $cannibalized_cigarettes_sales_ : 0)
                        , (!empty( $cigarettesales_ ) ? $cigarettesales_ : 0)
                    ) . ' %',
                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'alcohol' ] [ 'quarters' ][ 'alcohol' ] [] =
                [

                    'sale' => $quaterString . " " . $rows->year . ' Alcohol Sales: $' . Helper::numberFormatShort (
                            $rows->alcohol,
                            2
                        ),
                    'cannibalized_sale' => $quaterString . " " . $rows->year . ' Cannibalized Alcohol Sales: $' .
                        Helper::numberFormatShort (
                            $rows->cannibalized_alcohol
                        ),
                    'cannibalization' => $quaterString . " " . $rows->year . ' Cannibalization: ' .
                        $this->percentageCalculation (
                            (!empty( $rows->cannibalized_alcohol ) ? $rows->cannibalized_alcohol : 0)
                            , (!empty( $rows->alcohol ) ? $rows->alcohol : 0)
                        ) . ' %',
                    'cannibalization_total' => $this->percentageCalculation (
                        (!empty( $rows->cannibalized_alcohol ) ? ($rows->cannibalized_alcohol) : 0),
                        (!empty( $rows->alcohol ) ? ($rows->alcohol) : 0)
                    )

                ];  ///

            $data[ 'state' ][ $rows->state_ABV ] [ 'alcohol' ] [ 'quarters' ][ 'alcohol_total' ] [] =
                [

                    'sale' => Helper::numberFormatShort ( $rows->alcohol, 2 ),
                    'cannibalized_sale' => Helper::numberFormatShort ( $rows->cannibalized_alcohol ),
                    'cannibalization' => $this->percentageCalculation (
                            (!empty( $rows->cannibalized_alcohol ) ? $rows->cannibalized_alcohol : 0)
                            , (!empty( $rows->alcohol ) ? $rows->alcohol : 0)
                        ) . ' %'

                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'alcohol' ] [ 'summary' ][] =

                [
                    'quarter' => $quaterString . " " . mb_substr ( $rows->quarter, 3, 4 ),
                    'field1' => (float)$rows->alcohol,
                    'field2' => (float)$rows->cannibalized_alcohol,
                    'field1_label' => Helper::numberFormatShort ( $rows->alcohol, 2 ),
                    'field2_label' => Helper::numberFormatShort ( $rows->cannibalized_alcohol, 2 ),
                ];

            /* $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'quarters' ][ 'tobacco' ] [] =
                 [

                     'sale' => $quaterString . " " . $rows->year . ' Tobacco Sales: $' . Helper::number_format_short ( $rows->cannibalized_tobacco, 2 ),
                     'cannibalized_sale' => $quaterString . " " . $rows->year . ' Cannibalized  Sales: $' . Helper::number_format_short ( $rows->cannibalized_tobacco, 2 ),
                     'cannibalization' => $quaterString . " " . $rows->year . ' Cannibalization: ' . $this->percetnageCalculation ( (!empty( $rows->cannibalized_tobacco ) ? $rows->cannibalized_tobacco : 0)
                             , (!empty( $rows->tobacco ) ? $rows->tobacco : 0)
                         ) . ' %',
                     'cannibalization_total' => $this->percetnageCalculation ( (!empty( $rows->cannibalized_tobacco ) ? $rows->cannibalized_tobacco : 0)
                         , (!empty( $rows->tobacco ) ? $rows->tobacco : 0) )
                 ];
 */

            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'quarters' ][ 'tobacco' ] [] =
                [

                    'sale' => $quaterString . " " . $rows->year . ' Tobacco Sales: $' .
                        Helper::numberFormatShort (
                            $rows->tobacco,
                            2
                        ),
                    'cannibalized_sale' => $quaterString . " " . $rows->year . ' Cannibalized  Sales: $' .
                        Helper::numberFormatShort (
                            $rows->cannibalized_tobacco,
                            2
                        ),
                    'cannibalization' => $quaterString . " " . $rows->year . ' Cannibalization: ' .
                        $this->percentageCalculation (
                            (!empty( $rows->cannibalized_tobacco ) ? $rows->cannibalized_tobacco : 0)
                            , (!empty( $rows->tobacco ) ? $rows->tobacco : 0)
                        ) . ' %',
                    'cannibalization_total' =>
                        $this->percentageCalculation (
                            (!empty( $rows->cannibalized_tobacco ) ? $rows->cannibalized_tobacco : 0)
                            , (!empty( $rows->tobacco ) ? $rows->tobacco : 0)
                        )
                ];


            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'quarters' ][ 'cigarettes' ] [] =

                [
                    'sale' => $quaterString . " " . $rows->year . ' Cigarettes Sales: $' .
                        Helper::numberFormatShort (
                            $rows->cigarettes,
                            2
                        ),
                    'cannibalized_sale' => $quaterString . " " . $rows->year . ' Cannibalized Cigarettes Sales: $' .
                        Helper::numberFormatShort (
                            $rows->cannibalized_cigarettes,
                            2
                        ),
                    'cannibalization' => $quaterString . " " . $rows->year . ' Cannibalization: ' .
                        $this->percentageCalculation (
                            (!empty( $rows->cannibalized_cigarettes ) ? $rows->cannibalized_cigarettes : 0)
                            , (!empty( $rows->cigarettes ) ? $rows->cigarettes : 0)
                        ) . ' %',
                    'cannibalization_total' => $this->percentageCalculation (
                        (!empty( $rows->cannibalized_cigarettes ) ? $rows->cannibalized_cigarettes : 0)
                        , (!empty( $rows->cigarettes ) ? $rows->cigarettes : 0) )
                ];

            /* $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'quarters' ][ 'tobacco_total' ] [] =

                 [
                     'sale' => Helper::number_format_short ($rows->cannibalized_tobacco,2),
                     'cannibalized_sale' => Helper::number_format_short ($rows->cannibalized_tobacco,2),
                     'cannibalization' => $this->percetnageCalculation ( (!empty( $rows->cannibalized_tobacco ) ? $rows->cannibalized_tobacco : 0)
                         , (!empty( $rows->tobacco ) ? $rows->tobacco : 0)
                     )." %",

                 ];*/

            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'quarters' ][ 'tobacco_total' ] [] =
                [
                    'sale' => Helper::numberFormatShort ( $rows->tobacco, 2 ),
                    'cannibalized_sale' => Helper::numberFormatShort (
                        $rows->cannibalized_tobacco,
                        2
                    ),
                    'cannibalization' => $this->percentageCalculation (
                        (!empty( $rows->cannibalized_tobacco ) ? $rows->cannibalized_tobacco : 0)
                        , (!empty( $rows->tobacco ) ? $rows->tobacco : 0)
                    ),
                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'quarters' ][ 'cigarettes_total' ] [] =
                [
                    'sale' => Helper::numberFormatShort (
                        $rows->cigarettes,
                        2
                    ),
                    'cannibalized_sale' => Helper::numberFormatShort (
                        $rows->cannibalized_cigarettes,
                        2
                    ),
                    'cannibalization' => $this->percentageCalculation (
                        (!empty( $rows->cannibalized_cigarettes ) ? $rows->cannibalized_cigarettes : 0)
                        , (!empty( $rows->cigarettes ) ? $rows->cigarettes : 0)
                    ),

                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'summary' ][] =
                [
                    'quarter' => $quaterString . " " . mb_substr ( $rows->quarter, 3, 4 ),
                    'field1' => (float)$rows->tobacco,
                    'field2' => (float)$rows->cannibalized_tobacco,
                    'field1_label' => Helper::numberFormatShort ( $rows->tobacco, 2 ),
                    'field2_label' => Helper::numberFormatShort ( $rows->cannibalized_tobacco, 2 ),

                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'quarters' ][ 'non_cigarettes' ] [] =
                [
                    'sale' => $quaterString . " " . $rows->year . ' Non-Cigarettes Sales: $' .
                        Helper::numberFormatShort (
                            $rows->non_cigarettes,
                            2
                        ),
                    'cannibalized_sale' => $quaterString . " " . $rows->year . ' Cannibalized Non-Cigarettes Sales: $' .
                        Helper::numberFormatShort (
                            $rows->cannibalized_non_cigarettes,
                            2
                        ),
                    'cannibalization' => $quaterString . " " . $rows->year . ' Cannibalization: ' .
                        $this->percentageCalculation (
                            (!empty( $rows->cannibalized_non_cigarettes ) ? $rows->cannibalized_non_cigarettes : 0)
                            , (!empty( $rows->non_cigarettes ) ? $rows->non_cigarettes : 0)
                        ) . ' %',
                    'cannibalization_total' => $quaterString . " " . $rows->year . ' Cannibalization: ' .
                        $this->percentageCalculation (
                            (!empty( $rows->cannibalized_non_cigarettes ) ? $rows->cannibalized_non_cigarettes : 0)
                            , (!empty( $rows->non_cigarettes ) ? $rows->non_cigarettes : 0)
                        ),
                ];
            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'quarters' ][ 'non_cigarettes_total' ] [] =
                [


                    'sale' => Helper::numberFormatShort ( $rows->non_cigarettes, 2 ),
                    'cannibalized_sale' => Helper::numberFormatShort ( $rows->cannibalized_non_cigarettes, 2 ),
                    'cannibalization' => $this->percentageCalculation (
                        (!empty( $rows->cannibalized_non_cigarettes ) ? $rows->cannibalized_non_cigarettes : 0)
                        , (!empty( $rows->non_cigarettes ) ? $rows->non_cigarettes : 0)
                    ),

                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'alcohol' ] [ 'ytd_data' ][ 'alcohol' ] =
                [
                    $rows->year . ' YTD Alcohol Sales: $' . Helper::numberFormatShort (
                        $ytdAlcholsales,
                        2 ),
                    $rows->year . ' YTD Cannibalized Alcohol Sales: $' . Helper::numberFormatShort (
                        $canbblizationAlcholsales
                    ),
                    $rows->year . ' YTD Cannibalization: ' . $this->percentageCalculation (
                        (!empty( $canbblizationAlcholsales ) ? $canbblizationAlcholsales : 0)
                        , (!empty( $ytdAlcholsales ) ? $ytdAlcholsales : 0)
                    ) . ' %',
                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'alcohol' ] [ 'ytd_data' ][ 'total' ] =
                [
                    'sale' => Helper::numberFormatShort ( $ytdAlcholsales ),
                    'cannibalized_sale' => Helper::numberFormatShort ( $canbblizationAlcholsales ),
                    'cannibalization' => $this->percentageCalculation (
                            (!empty( $canbblizationAlcholsales ) ? $canbblizationAlcholsales : 0)
                            , (!empty( $ytdAlcholsales ) ? $ytdAlcholsales : 0)
                        ) . ' %'
                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'ytd_data' ][ 'cigarettes_total' ] =
                [
                    'sale' => Helper::numberFormatShort ( $cigarettesales_, 2 ),
                    'cannibalized_sale' => Helper::numberFormatShort ( $cannibalized_cigarettes_sales_, 2 ),
                    'cannibalization' => $this->percentageCalculation (
                            (!empty( $cannibalized_cigarettes_sales_ ) ? $cannibalized_cigarettes_sales_ : 0)
                            , (!empty( $cigarettesales_ ) ? $cigarettesales_ : 0)
                        ) . " %"
                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'ytd_data' ][ 'non_cigarettes' ] =
                [
                    $rows->year . ' YTD Non-Cigarettes Sales: $' .
                    Helper::numberFormatShort (
                        $non_cigarettesales,
                        2
                    ),
                    $rows->year . ' YTD Cannibalized Non-Cigarettes Sales: $' .
                    Helper::numberFormatShort (
                        $cannibalized_non_cigarettesales,
                        2
                    ),
                    $rows->year . ' YTD Cannibalization: ' . $this->percentageCalculation (
                        (!empty( $cannibalized_non_cigarettesales ) ? $cannibalized_non_cigarettesales : 0)
                        , (!empty( $non_cigarettesales ) ? $non_cigarettesales : 0)
                    ) . ' %',
                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'ytd_data' ][ 'non_cigarettes_total' ] =
                [
                    'sale' => Helper::numberFormatShort ( $non_cigarettesales, 2 ),
                    'cannibalized_sale' => Helper::numberFormatShort ( $cannibalized_non_cigarettesales, 2 ),
                    'cannibalization' => $this->percentageCalculation (
                            (!empty( $cannibalized_non_cigarettesales ) ? $cannibalized_non_cigarettesales : 0)
                            , (!empty( $non_cigarettesales ) ? $non_cigarettesales : 0)
                        ) . ' %',
                ];


            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'ytd_data' ][ 'tobacco' ] =
                [
                    $rows->year . ' YTD Tobacco Sales: $' .
                    Helper::numberFormatShort (
                        $tobacco_sales,
                        2
                    ),
                    $rows->year . ' YTD Cannibalized Tobacco Sales: $' .
                    Helper::numberFormatShort (
                        $cannibalized_tobacco_sales,
                        2
                    ),
                    $rows->year . ' YTD Cannibalization: ' . $this->percentageCalculation (
                        (!empty( $cannibalized_tobacco_sales ) ? $cannibalized_tobacco_sales : 0)
                        , (!empty( $tobacco_sales ) ? $tobacco_sales : 0)
                    ) . ' %',
                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'tobacco' ] [ 'ytd_data' ][ 'tobacco_total' ] =
                [
                    'sale' => Helper::numberFormatShort ( $tobacco_sales, 2 ),
                    'cannibalized_sale' => Helper::numberFormatShort ( $cannibalized_tobacco_sales, 2 ),
                    'cannibalization' => $this->percentageCalculation (
                            (!empty( $cannibalized_tobacco_sales ) ? $cannibalized_tobacco_sales : 0)
                            , (!empty( $tobacco_sales ) ? $tobacco_sales : 0)
                        ) . ' %',
                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'pharma' ] [ 'quarters' ][ 'pharma' ] [] =
                [
                    'sale' => $quaterString . " " . $rows->year . ' Pharma Sales: $' .
                        Helper::numberFormatShort (
                            $rows->pharma,
                            2 ),
                    'cannibalized_sale' => $quaterString . " " . $rows->year . ' Cannibalized Pharma Sales: $' .
                        Helper::numberFormatShort (
                            $rows->cannibalized_pharma,
                            2 ),
                    'cannibalization' => $quaterString . " " . $rows->year . ' Cannibalization: ' . $this->percentageCalculation (
                            (!empty( $rows->cannibalized_pharma ) ? $rows->cannibalized_pharma : 0)
                            , (!empty( $rows->pharma ) ? $rows->pharma : 0)
                        ) . ' %',
                    'cannibalization_total' => $this->percentageCalculation (
                        (!empty( $rows->cannibalized_pharma ) ? $rows->cannibalized_pharma : 0)
                        , (!empty( $rows->pharma ) ? $rows->pharma : 0)
                    )
                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'pharma' ] [ 'quarters' ][ 'pharma_total' ] [] =
                [
                    'sale' => Helper::numberFormatShort ( $rows->pharma, 2 ),
                    'cannibalized_sale' => Helper::numberFormatShort ( $rows->cannibalized_pharma, 2 ),
                    'cannibalization' => $this->percentageCalculation (
                            (!empty( $rows->cannibalized_pharma ) ? $rows->cannibalized_pharma : 0)
                            , (!empty( $rows->pharma ) ? $rows->pharma : 0)
                        ) . ' %',
                ];

            $data[ 'state' ][ $rows->state_ABV ] [ 'pharma' ] [ 'ytd_data' ][ 'pharma' ] =
                [
                    $rows->year . ' YTD Pharma Sales: $' . Helper::numberFormatShort (
                        $pharma_sales,
                        2
                    ),
                    $rows->year . ' YTD Cannibalized Pharma Sales: $' . Helper::numberFormatShort (
                        $cannibalized_pharma_sales,
                        2
                    ),
                    $rows->year . ' YTD Cannibalization: ' . $this->percentageCalculation (
                        (!empty( $cannibalized_pharma_sales ) ? $cannibalized_pharma_sales : 0)
                        , (!empty( $pharma_sales ) ? $pharma_sales : 0)
                    ) . ' %',
                ];
            $data[ 'state' ][ $rows->state_ABV ] [ 'pharma' ] [ 'ytd_data' ] [ 'total' ] =
                [
                    'sale' => Helper::numberFormatShort ( $pharma_sales, 2 ),
                    'cannibalized_sale' => Helper::numberFormatShort ( $cannibalized_pharma_sales, 2 ),
                    'cannibalization' => $this->percentageCalculation (
                            (!empty( $cannibalized_pharma_sales ) ? $cannibalized_pharma_sales : 0)
                            , (!empty( $pharma_sales ) ? $pharma_sales : 0)
                        ) . ' %',
                ];
            $data[ 'state' ][ $rows->state_ABV ] [ 'pharma' ] [ 'summary' ][] =
                [
                    'quarter' => $quaterString . " " . mb_substr ( $rows->quarter, 3, 4 ),
                    'field1' => (float)$rows->pharma,
                    'field2' => (float)$rows->cannibalized_pharma,
                    'field1_label' => Helper::numberFormatShort ( $rows->pharma, 2 ),
                    'field2_label' => Helper::numberFormatShort ( $rows->cannibalized_pharma, 2 ),
                ];
        }
        $data[ 'total' ] = $this->calculationByQuarter ( $calculationByQuarter, $allQuater );
        if(empty ($data['total']) ){
          return false;
        }

        return $data;
    }

    public function calculationByQuarter($quater = true, $QuaterArr = [])
    {

        $objHelper = new Helper();
        $datasetId = Config::get ( 'custom_config.data_set' )[ 'cannibalization' ];
        $dataSetId = $objHelper->getDataSetId ( Cannibalization::$table_connection, $datasetId )  ;

        if ( $quater  && !empty($dataSetId)  ) {
            $result = DB::connection ( 'mysql_external_intake' )->select ( DB::raw ( "
               SELECT sum(alcohol)alcohol, sum(cannibalized_alcohol)canna_alcohol,
               sum(tobacco)tobacco,sum(cannibalized_tobacco)canna_tobacco ,
               sum(pharma)pharma ,sum(cannibalized_pharma)canna_pharma,quarter  FROM cannibalization 
               Where  dataset_id != '0'
               and  dataset_id != ''
               and  dataset_id = $dataSetId
               GROUP by quarter
        " ) );

        } else {

            $result = DB::connection ( 'mysql_external_intake' )->select ( DB::raw ( "
               SELECT sum(alcohol)alcohol, sum(cannibalized_alcohol)canna_alcohol,
               sum(tobacco)tobacco,sum(cannibalized_tobacco)canna_tobacco ,
               sum(pharma)pharma ,sum(cannibalized_pharma)canna_pharma,quarter  FROM cannibalization 
               GROUP by quarter
        " ) );

        }
        $data = [];
        if ( empty( $result ) ) {

            return $data;
        }
        $i = null;
        $calc = 0;
        $a = array();
        foreach ( $result as $rows ) {
            if ( !empty( $QuaterArr ) ) {
                if ( in_array ( $rows->quarter, $QuaterArr ) ) {
                    $data[ 'alcohol' ] [] =
                        [
                            'quarter' => substr ( $rows->quarter, 0, 2 ) . " " . mb_substr ( $rows->quarter, 3, 4 ),
                            'field1' => (float)$rows->alcohol,
                            'field2' => (float)$rows->canna_alcohol,
                            'field1_label' => Helper::numberFormatShort ( $rows->alcohol, 2 ),
                            'field2_label' => Helper::numberFormatShort ( $rows->canna_alcohol, 2 )
                        ];
                    $data [ 'tobacco' ] [] =
                        [
                            'quarter' => substr ( $rows->quarter, 0, 2 ) . " " . mb_substr ( $rows->quarter, 3, 4 ),
                            'field1' => (float)$rows->tobacco,
                            'field2' => (float)$rows->canna_tobacco,
                            'field1_label' => Helper::numberFormatShort ( $rows->tobacco, 2 ),
                            'field2_label' => Helper::numberFormatShort ( $rows->canna_tobacco, 2 )
                        ];
                    $data [ 'pharma' ] [] =
                        [
                            'quarter' => substr ( $rows->quarter, 0, 2 ) . " " . mb_substr ( $rows->quarter, 3, 4 ),
                            'field1' => (float)$rows->pharma,
                            'field2' => (float)$rows->canna_pharma,
                            'field1_label' => Helper::numberFormatShort ( $rows->pharma, 2 ),
                            'field2_label' => Helper::numberFormatShort ( $rows->canna_pharma, 2 )
                        ];
                }
            }
        }
        return $data;
    }


    public function percentageCalculation($firstVal, $secondVal)
    {
        if ( $secondVal == 0 ) {
            return 0;
        }

        $percentage = ($firstVal / $secondVal) * 100;
        $percentage = number_format ( (float)$percentage, 1, '.', '' );

        return $percentage;
    }

}
