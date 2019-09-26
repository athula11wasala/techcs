<?php

namespace App\Http\Controllers;

use App\Services\TickerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class TickerController extends ApiController
{
    private $tickerService;

    public function __construct(TickerService $tickerService) {
        $this->tickerService = $tickerService;
    }

    public function getCharts(Request $request) {
        \Log::info("==== TickerController->getCharts ", ['u' => json_encode($request)]);
        $index = $request->index;
        $charts = $this->tickerService->getCharts($index);
        $response = response()->json(['charts' => $charts], 200);
        return $response;
    }

    public function getDetails(Request $request) {
        \Log::info("==== TickerController->getDetails ", ['u' => json_encode($request)]);
        $index = $request->index;
        $details = $this->tickerService->getDetails($index);
        $response = response()->json(['details' => $details], 200);
        return $response;
    }

    public function todayTickerData(Request $request) {
        $dailyTickerData = $this->tickerService->getDailyTickerData();
        return $this->respond($dailyTickerData);
    }

    public function getIndex(Request $request) {
        \Log::info("==== TickerController->getIndex ", ['u' => json_encode(['index'=>$request->index, 'period'=>$request->period])]);
        $charts = $this->tickerService->getIndex($request->index,$request->period);
        $response = response()->json(['charts' => $charts], 200);
        return $response;
    }

    public function getSectorName(Request $request) {
        \Log::info("==== TickerController->getSectorName ", ['u' => json_encode($request)]);
        $name = $this->tickerService->getSectorIndexName($request->index);
        $response = response()->json(['name' => $name], 200);
        return $response;
    }

    public function getSectorIndex(Request $request) {
        \Log::info("==== TickerController->getSectorIndex ", ['u' => json_encode($request)]);
        $charts = $this->tickerService->getSectorIndex($request->index,$request->period);
        $response = response()->json(['charts' => $charts], 200);
        return $response;
    }

    public function getTopBottomMovers(Request $request) {
        \Log::info("==== TickerController->getTopBottomMovers ", ['u' => json_encode($request)]);
        $index = $request->index;
        $movers = $this->tickerService->getTopBottomMovers($index);
        $response = response()->json(['movers' => $movers], 200);
        return $response;
    }

    public function getSectorTopBottomMovers(Request $request) {
        \Log::info("==== TickerController->getSectorTopBottomMovers ", ['u' => json_encode($request)]);
        $index = $request->index;
        $movers = $this->tickerService->getSectorTopBottomMovers($index);
        $response = response()->json(['movers' => $movers], 200);
        return $response;
    }

    public function getSectorSummary() {
        \Log::info("==== TickerController->getSectorSummary ", ['u' => json_encode(null)]);
        $movers = $this->tickerService->getSectorSummary();
        $response = response()->json(['summary' => $movers], 200);
        return $response;
    }

    public function getCompanyList(Request $request) {
        \Log::info("==== TickerController->getCompanyList ", ['u' => json_encode($request)]);
        $list = $this->tickerService->getCompanyList();
        $response = response()->json(['list' => $list], 200);
        return $response;
    }

    /**
     * get company report pdf using external url
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCompanyReport(Request $request)
    {
        \Log::info("==== TickerController->getCompanyReport ", ['u' => json_encode($request->all())]);

        $link = url(Config::get ( 'custom_config.WOPRAI_WEEDEX_EXTERNAL_LINK' )) . '?';
        $link .= http_build_query([
            'user' => 'nfdata',
            'password' => 'dNFront1',
            'object' => 'report',
            'action' => 'get',
            'asset' => $request['asset'],
            'index' => 'cannabis',
            'format' => 'pdf'
        ]);

        // read the content of file
        $readTheFile = file_get_contents($link);

        // set headers
        $headers = [
            'Content-Description: File Transfer',
            'Content-Type: application/pdf',
        ];

        return response()->make($readTheFile, 200, $headers);
    }

    /**
     * get company report pdf using external url
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getShortPositions(Request $request) {
        \Log::info("==== TickerController->getShortPositions ", ['u' => json_encode($request->all())]);

        $link = url(Config::get ( 'custom_config.WOPRAI_WEEDEX_EXTERNAL_LINK' )) . '?';
        $link .= http_build_query([
            'user' => 'nfdata',
            'password' => 'dNFront1',
            'object' => 'shorts',
            'action' => 'get',
            'asset' => $request['asset'],
            'date[from]' => date("Y-m-d",strtotime("-1 year")),
            'date[to]' => date("Y-m-d"),
            'format' => 'json',
        ]);
        \Log::info("==== TickerController->getShortPositions link ", ['u' => json_encode($link)]);
        \Log::info("==== TickerController->getShortPositions date ", ['u' => json_encode(date("Y-m-d"))]);
        \Log::info("==== TickerController->getShortPositions date ", ['u' => json_encode(date("Y-m-d",strtotime("-1 year")))]);

        // read the content of file
        $json = json_decode(file_get_contents($link), true)['data'];
        \Log::info("==== TickerController->getShortPositions json ", ['u' => json_encode($json)]);

        /* modify when fusioncharts is released */
        $series = array();
        foreach ($json as $bucket) {
            $element['name'] = $bucket['date'];
            $bucket['value'] = 0;
            if( $bucket['total'] > 0 ) {
                $element['value'] = $bucket['size'] / $bucket['total'];
            }
            $series[] = $element;
        }
        $result = array();
        if(count($series) >= 7) {
            $multi['name'] = $request['asset'];
            $multi['series'] = array_slice($series, -7, 7);
            $result['charts']['days']['multi'][] = $multi;
        } else {
            $multi['name'] = $request['asset'];
            $multi['series'] = $series;
            $result['charts']['days']['multi'][] = $multi;
        }
        if(count($series) >= 70) {
            $multi['name'] = $request['asset'];
            $multi['series'] = array_slice($series, -70, 70);
            $result['charts']['months']['multi'][] = $multi;
        } else {
            $multi['name'] = $request['asset'];
            $multi['series'] = $series;
            $result['charts']['months']['multi'][] = $multi;
        }
        $multi['name'] = $request['asset'];
        $multi['series'] = $series;
        $result['charts']['history']['multi'][] = $multi;

        return $result;
    }

    /**
     * get company report pdf using external url
     * need UsersController@getShortTrackerList for security!!!!!!!
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getShortPositionTable(Request $request) {
        \Log::info("==== TickerController->getShortPositionTable ", ['u' => json_encode($request->all())]);

        $link = url(Config::get ( 'custom_config.WOPRAI_WEEDEX_EXTERNAL_LINK' )) . '?';
        $list = array_filter(explode (",", $request['list']));
        \Log::info("==== TickerController->getShortPositionTable ", ['u' => json_encode($list)]);
        $result = array();
        foreach ($list as $asset) {
            $link .= http_build_query([
                'user' => 'nfdata',
                'password' => 'dNFront1',
                'object' => 'shorts',
                'action' => 'get',
                'asset' => $asset,
                'date[from]' => date("Y-m-d",strtotime("-4 day")),
                'date[to]' => date("Y-m-d"),
                'format' => 'json',
            ]);
            \Log::info("==== TickerController->getShortPositionTable link ", ['u' => json_encode($link)]);

            // read the content of file
            $json = json_decode(file_get_contents($link), true)['data'];
            \Log::info("==== TickerController->getShortPositionTable json ", ['u' => json_encode($json)]);

            /* modify when fusioncharts is released */
            if(count($json) > 0) {
                $lastEl = array_values(array_slice($json, -1))[0];
                $result[] = ['asset'=>$asset, 'value'=>$lastEl];
            }
        }
        \Log::info("==== TickerController->getShortPositionTable result ", ['u' => json_encode($result)]);

        return $result;
    }


}
