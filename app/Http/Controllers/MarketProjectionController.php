<?php

namespace App\Http\Controllers;

use App\Services\MarketProjectionService;
use Illuminate\Http\Request;

class MarketProjectionController extends ApiController
{
    public function __construct(MarketProjectionService $marketProjectionService)
    {
        $this->marketProjectionService = $marketProjectionService;
    }

    public function getCharts()
    {
        $charts = $this->marketProjectionService->getCharts();
        $response = response()->json(['charts' => $charts], 200);
        return $response;
    }
}
