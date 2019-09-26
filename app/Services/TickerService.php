<?php

namespace App\Services;

use App\Repositories\TickerRepository;
use Join;
use Illuminate\Support\Facades\Config;
use DateTime;

class TickerService
{
    private $tickerRepository;

    /**
     * TickerService constructor.
     * @param $tickerRepository
     */
    public function __construct(TickerRepository $tickerRepository) {
        $this->tickerRepository = $tickerRepository;
    }

    /**
     * Returns the list of chart data for [today, 5 days, 3 months, and all history]
     * @param $index : the stock code
     * @return mixed
     */
    public function getCharts($index) {
        $charts = array(
            'today' => $this->tickerRepository->getChart($index, "minute", "8h"),
            'days' => $this->tickerRepository->getChart($index, "hour", "5d"),
            'months' => $this->tickerRepository->getChart($index, "day", "93d"),
            'history' => $this->tickerRepository->getChart($index, "week"),
        );
        return $charts;
    }

    /**
     * Returns the array of symbol/asset+trade/quote : stock details
     * @param $index
     * @return mixed
     */
    public function getDetails($index) {
        $details = array_merge($this->tickerRepository->getDetails($index),$this->tickerRepository->getTradeData($index));
        return $details;
    }

    /**
     * Returns summary of the index
     * @param $index : the stock code name
     * @return mixed
     */
    public function getCompanyList() {
        $list = $this->tickerRepository->getCompanyList();
        return $list;
    }

    /**
     * get all ticker data without last and open price null
     *
     * @return mixed
     */
    public function getDailyTickerData() {
        $details = $this->tickerRepository->getDailyTickerData();
        return $details;
    }

    /**
     * Returns the list of chart data for [today, 5 days, 3 months, and all history]
     * @param $index : the stock code
     * @return mixed
     */
    public function getIndex($index, $period) {
        $index_list = $this->tickerRepository->getAllTickersFromIndex($index);
        //$index_list = $this->tickerRepository->getAllTickers();
        //$index_list = ["ACAN","AVT","GRNH","CBMJ","ZYNE"];
        $charts = array();
        switch($period){
            case "null":
                $charts = array(
                    'today' => $this->tickerRepository->getIndex($index_list, "minute", "8h"),
                    'days' => $this->tickerRepository->getIndex($index_list, "hour", "7d"),
                    'months' => $this->tickerRepository->getIndex($index_list, "week", "93d"),
                    'history' => $this->tickerRepository->getIndex($index_list, "month"),
                );
                break;
            case 'today':
                $charts = array('today' => $this->tickerRepository->getIndex($index_list, "minute", "8h"));
                break;
            case 'days':
                $charts = array('days' => $this->tickerRepository->getIndex($index_list, "hour", "7d"));
                break;
            case 'months':
                $charts = array('months' => $this->tickerRepository->getIndex($index_list, "week", "93d"));
                break;
            case 'history':
                $charts = array('history' => $this->tickerRepository->getIndex($index_list, "month"));
                break;
        }
        return $charts;
    }

    /**
     * Returns the list of sector chart data for [today, 5 days, 3 months, and all history]
     * @param $index : the stock code
     * @return mixed
     */
    public function getSectorIndexName($index) {
        $name = $this->tickerRepository->getSectorFromTicker($index);
        return $name;
    }

    /**
     * Returns the list of sector chart data for [today, 5 days, 3 months, and all history]
     * @param $index : the stock code
     * @return mixed
     */
    public function getSectorIndex($index, $period) {
        $sector = $this->tickerRepository->getSectorFromTicker($index);
        $index_list = $this->tickerRepository->getAllTickersFromSector($sector);
        $charts = array();
        switch($period){
            case "null":
                $charts = array(
                    'today' => $this->tickerRepository->getIndex($index_list, "minute", "8h"),
                    'days' => $this->tickerRepository->getIndex($index_list, "hour", "7d"),
                    'months' => $this->tickerRepository->getIndex($index_list, "week", "93d"),
                    'history' => $this->tickerRepository->getIndex($index_list, "month"),
                );
                break;
            case 'today':
                $charts = array('today' => $this->tickerRepository->getIndex($index_list, "minute", "8h"));
                break;
            case 'days':
                $charts = array('days' => $this->tickerRepository->getIndex($index_list, "hour", "7d"));
                break;
            case 'months':
                $charts = array('months' => $this->tickerRepository->getIndex($index_list, "week", "93d"));
                break;
            case 'history':
                $charts = array('history' => $this->tickerRepository->getIndex($index_list, "month"));
                break;
        }
        return $charts;
    }

    /**
     * Returns summary of the index
     * @return mixed All summaries for every sectors
     */
    public function getSectorSummary() {
        $summaries = [];
        $sectors = $this->tickerRepository->getAllSectors();
        foreach($sectors as $sector){
            $index_list = $this->tickerRepository->getAllTickersFromSector($sector);
            $summary = $this->tickerRepository->getSectorSummary($index_list);
            $summary['name'] = $sector;
            $summaries[] = $summary;
        }
        return $summaries;
    }

    /**
     * Returns the list of the top movers and the list of the bottom movers
     * @param $index : the stock code name
     * @return mixed
     */
    public function getTopBottomMovers($index) {
        $index_list = $this->tickerRepository->getAllTickersFromIndex($index);
        $movers = $this->tickerRepository->getTopBottomMovers($index_list);
        return $movers;
    }

    /**
     * Returns the list of the top movers and the list of the bottom movers
     * @param $index : the stock code name
     * @return mixed
     */
    public function getSectorTopBottomMovers($index) {
        $sector = $this->tickerRepository->getSectorFromTicker($index);
        $index_list = $this->tickerRepository->getAllTickersFromSector($sector);
        $movers = $this->tickerRepository->getTopBottomMovers($index_list);
        return $movers;
    }

}


