<?php

namespace App\Http\Controllers;

use App\Services\DashBoardService;
use Illuminate\Http\Request;

class TaxAlertsController extends ApiController
{

    private $dashBoardService;

    public function __construct(DashBoardService $dashBoardService)
    {
//        $this->middleware('auth:api');
        $this->dashBoardService = $dashBoardService;
    }

    public function index(Request $request) {
        $taxAlerts = $this->dashBoardService->getTaxAlerts($request);
        return $this->respond($taxAlerts);
    }
}
