<?php

namespace App\Services;

use App\Repositories\TickerRepository;
use Join;
use DateTime;
use Illuminate\Support\Facades\Config;

class NewsService
{
    private $tickerRepository;

    /**
     * NewsService constructor.
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
    public function getIndexNews($index) {
        $index_list = $this->tickerRepository->getAllTickersFromIndex($index);
        $url = "https://api.benzinga.com/api/v2/news?tickers=".implode(",",$index_list)."&token=d06282cc5f0d4f89bbef81f8cd3191aa";
        \Log::info("==== NewsService->getIndexNews index_list=", ['u' => json_encode($url)]);
        $json = json_decode(file_get_contents($url), true);
        $result = [];
        foreach($json as $item) {
            $object["date"] = $item['created'];
            $object["title"] = $item['title'];
            $object["url"] = $item['url'];
            $result[] = $object;
        }
        return array_reverse($result);;
    }

    /**
     * Returns the list of chart data for [today, 5 days, 3 months, and all history]
     * @param $index : the stock code
     * @return mixed
     */
    public function getCompanyNews($index) {
        $url = "https://api.benzinga.com/api/v2/news?tickers=".$index."&token=d06282cc5f0d4f89bbef81f8cd3191aa";
        \Log::info("==== NewsService->getCompanyNews index_list=", ['u' => json_encode($url)]);
        $json = json_decode(file_get_contents($url), true);
        $result = [];
        foreach($json as $item) {
            $object["date"] = $item['created'];
            $object["title"] = $item['title'];
            $object["url"] = $item['url'];
            $result[] = $object;
        }
        return array_reverse($result);;
    }

    /**
     * Returns the list of chart data for [today, 5 days, 3 months, and all history]
     * @param $index : the stock code
     * @return mixed
     */
    public function getCompanySectorNews($index) {
        $sector = $this->tickerRepository->getSectorFromTicker($index);
        $index_list = $this->tickerRepository->getAllTickersFromSector($sector);
        $url = "https://api.benzinga.com/api/v2/news?tickers=".implode(",",$index_list)."&token=d06282cc5f0d4f89bbef81f8cd3191aa";
        \Log::info("==== NewsService->getCompanySectorNews index_list=", ['u' => json_encode($url)]);
        $json = json_decode(file_get_contents($url), true);
        $result = [];
        foreach($json as $item) {
            $object["date"] = $item['created'];
            $object["title"] = $item['title'];
            $object["url"] = $item['url'];
            $result[] = $object;
        }
        return array_reverse($result);;
    }

}


