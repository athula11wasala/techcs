<?php

namespace App\Http\Controllers;

use App\Services\CmsService;
use App\Services\DashBoardService;
use App\Services\InsightDailyUsService;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DatePeriod;
use Illuminate\Http\Request;
use App\Traits\InsighDalyValidators;
use App\Equio\Helper;
use Illuminate\Support\Facades\Config;

class TopFiveController extends ApiController
{
    use InsighDalyValidators;

    private $cmsService;
    private $dashBoardService;
    private $insightDailyUsService;

    /**
     * UsersController constructor.
     * @param cmsService $cmsService
     */
    public function __construct(Cmsservice $cmsService, DashBoardService $dashBoardService, InsightDailyUsService $insightDailyUsService)
    {
        $this->cmsService = $cmsService;
        $this->dashBoardService = $dashBoardService;
        $this->insightDailyUsService = $insightDailyUsService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $topFiveObject = $this->dashBoardService->getTopFive ();

        if ( $topFiveObject ) {
            return $this->respond ( $topFiveObject );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );


    }

    public function getAllNews()
    {
        $newsYear = $this->dashBoardService->getLatestNews ();
        $lastDate = $newsYear[ 'last_year' ];
        $firstDate = $newsYear[ 'start_year' ];
        $newsYear[ 'year_search' ] = $this->createMonthSearchMap ( $lastDate[ 'date' ], $firstDate[ 'date' ] );
        return $this->respond ( $newsYear );
    }

    public function getSearchedNews(Request $request)
    {
        $news = $this->dashBoardService->getAllSearchedNews ( $request );
        $lastDate = $news[ 'last_year' ];
        $firstDate = $news[ 'start_year' ];
        $newsYear[ 'year_search' ] = $this->createMonthSearchMap ( $lastDate[ 'date' ], $firstDate[ 'date' ] );
        return $this->respond ( $news );
    }

    /**
     * Compute a range between two dates month by month, and generate
     * a plain array of Carbon objects of each day in it.
     *
     * @param  \Carbon\Carbon $from
     * @param  \Carbon\Carbon $to
     * @param  bool $inclusive
     * @return array|null
     *
     */
    function date_range(Carbon $from, Carbon $to, $inclusive = true)
    {
        if ( $from->gt ( $to ) ) {
            return null;
        }

        // Clone the date objects to avoid issues, then reset their time
        $from = $from->copy ()->startOfMonth ();
        $to = $to->copy ()->startOfMonth ();

        // Include the end date in the range
        if ( $inclusive ) {
            $to->addMonth ();
        }

        $step = CarbonInterval::month ();
        $period = new DatePeriod( $from, $step, $to );

        // Convert the DatePeriod into a plain array of Carbon objects
        $range = [];

        foreach ( $period as $day ) {
            $range[] = new Carbon( $day );
        }

        return !empty( $range ) ? $range : null;
    }


    private function createMonthSearchMap($lastDate, $firstDate)
    {
        $period = $this->date_range ( Carbon::parse ( $lastDate ), Carbon::parse ( $firstDate ) );

        foreach ( $period as $dt ) {
            $returnDataArray[ $dt->format ( "Y" ) ][] = ucwords ( $dt->format ( "F" ) );
        }

        krsort ( $returnDataArray );

        $i = 0;
        foreach ( $returnDataArray as $year => $month ) {
            $returnArr[ $i ][ 'year' ] = $year; //array_reverse($month);
            $returnArr[ $i ][ 'month' ] = array_reverse ( $month );
            ++$i;
        }

        return $returnArr;

    }

    public function createInsightDaily(Request $request)
    {

        $validator = $this->insightDailyValidate ( $request->all () );

        if ( $validator->fails () ) {

            $validateMessge = Helper::customErrorMsg ( $validator->messages () );

            return response ()->json ( ['error' => $validateMessge], 400 );

        }

        if ( $validator->passes () ) {
            $data = $this->insightDailyUsService->createInsightDaily ( $request );

            if ( $data ) {
                return response ()->json ( ['message' => __ ( 'messages.insight_daily_add_success' )], 200 );
            }

            return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

        }
    }

    public function editInsightDaily(Request $request)
    {
        $validator = $this->insightDailyValidate ( $request->all (), 'PUT' );

        if ( $validator->fails () ) {

            $validateMessge = Helper::customErrorMsg ( $validator->messages () );

            return response ()->json ( ['error' => $validateMessge], 400 );

        }

        if ( $validator->passes () ) {
            $data = $this->insightDailyUsService->getUpdateInsightDaily ( $request );
            if ( $data ) {
                return response ()->json ( ['message' => __ ( 'messages.insight_daily_edit_success' )], 200 );
            }

            return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

        }

    }

    public function searchInsightDaily(Request $request)
    {
        $insightDailyData = $this->insightDailyUsService->getAllInsightDetail ( $request );
        if ( $insightDailyData ) {
            return response ()->json ( ['data' => $insightDailyData], 200 );
        }
        return response ()->json ( ['data' => ['data' => $insightDailyData]], 400 );
    }

    public function getInsightDailyTopicWithImg(Request $request)
    {
        $data = $this->insightDailyUsService->getCategoryWithImg ();
        if ( $data ) {
            return response ()->json ( ['data' => $data], 200 );

        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function showInsightDaily($id = null)
    {

        $data = $this->insightDailyUsService->getInsightDailyById ( $id );
        if ( $data ) {
            return response ()->json ( ['data' => $data], 200 );

        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function deleteInsightDaily($id = null)
    {
        $validator = $this->insightDailyValidate(['id' => $id], 'DELETE');

        if ($validator->fails()) {

            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($validator->passes()) {
            $companyData = $this->insightDailyUsService->deleteInsightDaily(['id' => $id]);

            if ($companyData) {
                return response()->json(['message' => __('messages.insight_daily_delete_success')], 200);
            }
            return response()->json(['error' => __('messages.un_processable_request')], 400);

        }

    }


}
