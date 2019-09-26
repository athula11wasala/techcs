<?php
/**
 * Created by PhpStorm.
 * User: thilan
 * Date: 7/4/18
 * Time: 3:24 PM
 */

namespace App\Services;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Config;

class ElasticSearchService
{
    private $client;

    public function __construct()
    {
        $hosts = Config::get('elastic_config.connections.default.hosts');
        $this->client = ClientBuilder::create()
            ->setHosts($hosts)
            ->setSerializer('\Elasticsearch\Serializers\EverythingToJSONSerializer')
            ->build();
    }


    public function getTickerData($ticker, $paramArray)
    {
        return $this->getElasticData($ticker, $paramArray, $size = 1, $sortArray = null, $from = 0);
    }


    private function getElasticData($ticker, $paramArray, $size, $sortArray, $from)
    {
        $params = [
            'index' => $ticker,
            'body' => [
                'query' => $paramArray,
                'size' => $size,
                'sort' => $sortArray,
                'from' => $from,
            ]
        ];
        $results = $this->client->search($params);
        $this->tickerDataParser($results);

    }

    private function tickerDataParser($results)
    {
        $tickersData = collect();
        foreach ($results['hits']['hits'] as $result) {
            $ticker = collect();
            $ticker->put('company', $result['_source']['company']);
            $ticker->put('symbol', $result['_source']['symbol']);
            $ticker->put('last', $result['_source']['last']);
            $ticker->put('open', $result['_source']['open']);
            $ticker->put('volume', $result['_source']['volume']);
            $ticker->put('published_date', date('Y-m-d h:i:s', strtotime($result['_source']['published_date'])));
            $tickersData->push($ticker);
        }
        return $tickersData;
    }


}