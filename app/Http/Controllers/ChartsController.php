<?php

namespace App\Http\Controllers;

use App\Services\ChartsService;
use Illuminate\Http\Request;

class ChartsController extends ApiController
{

    /**
     * @var chartsService
     */
    private $chartsService;

    /**
     * ChartsController constructor.
     * @param ChartsService $chartsService
     */
    public function __construct(ChartsService $chartsService)
    {
        $this->chartsService = $chartsService;
    }

    public function index(Request $request)
    {

        $chartsData = $this->chartsService->getAllChart($request->all());

        return $this->respond($chartsData);
    }

    public function showKeywords(Request $request)
    {
        $keywordsData = $this->chartsService->getAllKeyWord($request->all());

        return $this->respond($keywordsData);
    }

    public function searchCharts(Request $request)
    {
        $chartsData = $this->chartsService->getSearchCharts($request->all());

        return $this->respond($chartsData);
    }

    public function getAllChartName()
    {
        $chartsData = $this->chartsService->getAllChartNames();

        return $this->respond($chartsData);
    }


   /* public function addChart(Request $request)
    {
        $chartsData = $this->chartsService->addCharts($request->all());

        return $this->respond($chartsData);
    }
   */

}


