<?php

namespace App\Services;

use App\Repositories\MarketProjectionRepository;
use Join;

class MarketProjectionService
{
    private $marketProjectionRepository;

    /**
     * MarketProjectionService constructor.
     * @param $marketProjectionRepository
     */
    public function __construct(MarketProjectionRepository $marketProjectionRepository)
    {
        $this->marketProjectionRepository = $marketProjectionRepository;
    }

    /**
     * Returns the chart data
     * @return mixed
     */
    public function getCharts()
    {
        $charts = $this->marketProjectionRepository->getCharts();
        return $charts;
    }
}


