<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashBoardService;

class SaleProjectionsController extends ApiController
{
    private $dashBoardService;

    /**
     * ChartsController constructor.
     * @param dashboardService $dashBoardService
     */

    public function __construct(DashBoardService $dashBoardService)
    {
        $this->dashBoardService = $dashBoardService;
    }

    public function saleProjectionDetails(Request $request)
    {

        $saleProjectionDetails = $this->dashBoardService->getSaleProjectionDetails($request);

        return $this->respond($saleProjectionDetails);
    }

}
