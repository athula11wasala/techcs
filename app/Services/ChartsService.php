<?php

namespace App\Services;

use App\Repositories\ChartsRepository;
use App\Repositories\Criteria\Users\OrderByCreated;
use App\Repositories\KeywordsRepository;
use Join;

class ChartsService
{

    private $chartsRepository;
    private $keywordsRepository;

    /**
     * ChartsService constructor.
     * @param $chartsRepository
     */
    public function __construct(ChartsRepository $chartsRepository, KeywordsRepository $keywordsRepository)
    {
        $this->chartsRepository = $chartsRepository;
        $this->keywordsRepository = $keywordsRepository;
    }

    /**
     * get all chart details
     * @return mixed
     */
    public function getAllChart($request)
    {
        return $this->chartsRepository->allChartInfo($request);
    }

    public function getSearchCharts($request)
    {
        return $this->chartsRepository->allSearchedCharts($request);
    }

    public function getAllKeyWord($request)
    {
        return $this->keywordsRepository->allKeywordsInfo($request);
    }

    /*public function addCharts($request)
    {
        return $this->chartsRepository->saveChart ( $request );
    }*/

    /**
     * get all chart name
     * @return mixed
     */
    public function getAllChartNames()
    {
        return $this->chartsRepository->getChartNames();
    }

    /**
     * get chart details by id
     * @param $id
     * @return mixed
     */
    public function getChartListFromReportId($id)
    {
        return $this->chartsRepository->getChartListFromReportId($id);
    }

    public function getChartById($id)
    {
        return $this->chartsRepository->getChartById($id);
    }

}


