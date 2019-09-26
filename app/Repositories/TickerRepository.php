<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Config;
use Elasticsearch\ClientBuilder;
use DB;
use App\Models\Ticker;
use \Datetime;

class TickerRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model() {
        return 'App\Models\Ticker';
    }

    /**
     * Returns the chart data for the specified index
     * @param $index : stock asset code
     * @param $period : period of the chart (from when)
     * @param $interval : time bucket interval (second, minute, hour, day, month)
     * @return mixed
     */
    public function getChart($index, $interval, $period=0) {
        $hosts = Config::get('elastic_config.connections.default.hosts');
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_quote_*',
            'body' => [
                "size" => 0,
                "query" => [
                    "bool" => [
                        "filter" => [
                            ["term" => ["asset"=> strtoupper($index)]]
                        ]
                    ]
                ],
                "aggs" => [
                    "data" => [
                        "date_histogram" => [
                            "field" => "quote_date",
                            "interval" => $interval,
                            "time_zone" => "America/New_York",
                            "min_doc_count" => 1
                        ],
                        "aggs" => [
                            "price" => [
                                "avg" => [
                                    "field" => "quote_last"
                                ]
                            ]
                        ]
                    ]
                ],
                "stored_fields" => [
                    "*"
                ]
            ]
        ];
        if($period){
            $params['body']["query"]["bool"]["filter"][] =
                ["range" => ["quote_date"=> ["gte"=> "now-".$period."/d"]]];
        }

        \Log::info("==== params", ['u' => json_encode($params)]);
        $response = $client->search($params);
        $series = array();
        foreach ($response['aggregations']['data']['buckets'] as $bucket) {
            //$element['name'] = date("Y-m-d h:i:s",strtotime($bucket['key_as_string']));
            if($bucket['price']['value']) {
                $element['name'] = $bucket['key_as_string'];
                $element['value'] = $bucket['price']['value'];
                $series[] = $element;
            }
        }
        $result = array();
        $multi['name'] = $index;
        $multi['series'] = $series;
        $result['multi'][] = $multi;
        $single['name'] = $index;
        $single['value'] = 0;
        $result['single'][] = $single;
        return $result;
    }

    /**
     * Returns the symbol/asset data for the specified index
     * @param $index : stock asset code
     * @return mixed
     */
    public function getDetails($index) {
        $hosts = Config::get('elastic_config.connections.default.hosts' );
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_asset_*',
            'body' => [
                "size" => 1,
                "sort" => [
                    "create_at" => "desc"
                ],
                "query" => [
                    "term" => [
                        "asset_code"=> strtoupper($index)
                    ]
                ]
            ]
        ];
        $response = $client->search($params);
        return $response['hits']['hits'][0]['_source'];
    }

    /**
     * Returns the list of companies in today's asset
     * @param $index : stock asset code
     * @return mixed
     */
    public function getCompanyList() {

        $hosts = Config::get ( 'elastic_config.connections.default.hosts' );
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $list = [];
        $params = [
            'index' => 'ticker_asset_*',
            'body' => [
                "size"=> 0,
                "aggs"=> [
                    "group"=> [
                        "terms"=> ["size"=>500,"field"=> "asset_code"],
                        "aggs"=> [
                            "group_docs"=> [
                                "top_hits"=> [
                                    "size"=> 1,"sort"=>[["asset_code"=> ["order"=> "asc"]]],
                                    "_source"=> ["asset_code", "asset_name"]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $response = $client->search($params);
        foreach ($response['aggregations']['group']['buckets'] as $bucket_hit) {
            $list[$bucket_hit['key']] = $bucket_hit['group_docs']['hits']['hits'][0]['_source'];
        }
        ksort($list);

        return $list;
    }

    /**
     * Returns the trade/quote data for the specified index
     * @param $index : stock quote code
     * @return mixed
     */
    public function getTradeData($index) {
        $hosts = Config::get ( 'elastic_config.connections.default.hosts' );
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_quote_*',
            'body' => [
                "size" => 1,
                "sort" => [
                    "quote_date" => "desc"
                ],
                "query" => [
                    "term" => [
                        "asset"=> strtoupper($index)
                    ]
                ]
            ]
        ];
        $response = $client->search($params);
        return $response['hits']['hits'][0]['_source'];
    }

    /**
     * Return the common divisor of an index
     * @param $index : stock quote code
     * @return mixed
     */
    public function getCommonDivisor($index_list) {
        $hosts = Config::get ( 'elastic_config.connections.default.hosts' );
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_quote_*',
            'body' => [
                "size"  => 0,
                "query" => [
                    "bool" => [
                        "filter" => [
                            "terms" => [
                                "asset" => $index_list
                            ]
                        ]
//                        ,
//                        "must" => [
//                            "range" => [
//                                "quote_date" => [
//                                    "gte" => "2018-01-01T00:00:01.000000"
//                                ]
//                            ]
//                        ]
                    ]
                ],
                "aggs" => [
                    "group_by_asset" => [
                        "terms" => [
                            "field" => "asset",
                            "size" => count($index_list)
                        ],
                        "aggs" => [
                            "earliest_date" =>[
                                "terms" => [
                                    "field" =>"quote_date",
                                    "order" =>["_key" =>"asc"],
                                    "size" =>1
                                ],
                                "aggs" => [
                                    "get_the_quote" => [
                                        "sum" => ["field" =>"asset_capitalization_outstanding"]
                                    ]
                                ]
                            ],
                            "quote_last_for_each_company" =>[
                                "sum_bucket" => [
                                    "buckets_path" => "earliest_date>get_the_quote"]
                            ]
                        ]
                    ],
                    "common_divisor" =>[
                        "sum_bucket" => [
                            "buckets_path" => "group_by_asset>quote_last_for_each_company"
                        ]
                    ]
                ]
            ]
        ];
        $response = $client->search($params);
        $divisor = $response['aggregations']['common_divisor']['value']/100;
        \Log::info("==== TickerRepository->getCommonDivisor CommonDivisor=$divisor");
        return $response['aggregations']['common_divisor']['value']/100;
    }

    public function getIndexBaseline2($index_list) {
        $hosts = Config::get('elastic_config.connections.default.hosts');
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_quote_*',
            'body' => [
                "size" => count($index_list),
                "query" => [
                    "bool" => [
                        "filter" => [
                            "terms" => [
                                "asset" => $index_list
                            ]
                        ],
                        "must" => [
                            "range" => [
                                "quote_date" => [
                                    "gte" => "2018-01-01T00:00:01.000000"
                                ]
                            ]
                        ]
                    ]
                ],
                "aggs" => [
                    "group_docs" => [
                        "top_hits" => [
                            "size" => 1,
                            "sort" => [[
                                "quote_date" => [
                                    "order" => "desc"
                                ]
                            ]]
                        ]
                    ]
                ],
                "_source" => ["asset", "quote_date", "quote_last", "quote_volume", "quote_last", "quote_open",
                    "quote_high", "quote_low", "quote_last_close", "asset_capitalization_outstanding"]
            ]
        ];
        $response = $client->search($params);
        $stock_array = [];
        foreach ($response['hits']['hits'] as $bucket_hit) {
            $stock_array[$bucket_hit["_source"]['asset']] = $bucket_hit["_source"];
        }
        return $stock_array;
    }

    public function getIndexBaseline($index_list, $period=0) {
        $hosts = Config::get('elastic_config.connections.default.hosts');
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_quote_*',
            'body' => [
                "size" => 0,
                "query" => [
                    "bool" => [
                        "filter" => [["terms"=> ["asset" => $index_list]]]
                    ]
                ],
                "aggs"=> [
                    "assets"=> [
                        "terms"=> [
                            "field"=> "asset",
                            "size"=> 500
                        ],
                        "aggs"=> [
                            "earliest_record"=> [
                                "top_hits"=> [
                                    "size"=> 1,
                                    "_source" => ["asset", "quote_date", "asset_capitalization_outstanding"]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        if($period){
            $params['body']["query"]["bool"]["filter"][] = ["range" => ["quote_date"=> ["lte"=> "now-".$period."/d"]]];
            $params['body']['aggs']['assets']['aggs']['earliest_record']['top_hits']['sort'][] = ["quote_date"=> ["order"=> "desc"]];
        } else {
            $params['body']['aggs']['assets']['aggs']['earliest_record']['top_hits']['sort'][] = ["quote_date"=> ["order"=> "asc"]];
        }
        $response = $client->search($params);
        $stock_array = [];
        foreach ($response['aggregations']['assets']['buckets'] as $bucket) {
            if($bucket['earliest_record']['hits']['hits'][0]['_source']['asset_capitalization_outstanding']){
                $stock_array[$bucket['key']] = $bucket['earliest_record']['hits']['hits'][0]['_source'];
            }
        }
        return $stock_array;
    }

    /**
     * Returns the financial index data for the specified index
     * @param $index : stock index name
     * @return mixed
     */
    public function getIndex2($index_list, $interval, $period=0) {
        $hosts = Config::get ( 'elastic_config.connections.default.hosts' );
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_quote_*',
            'body' => [
                "size" => 0,
                "query" => [
                    "bool" => [
                        "filter" => [["terms"=> ["asset" => $index_list]]]
                    ]
                ],
                "aggs" => [
                    "group_by_date" => [
                        "date_histogram" => [
                            "field" => "quote_date",
                            "interval" => $interval,
                            "time_zone" => "America/New_York",
                            "min_doc_count" => 1
                        ],
                        "aggs" => [
                            "group_by_asset" => [
                                "terms" => [
                                    "field" => "asset",
                                    "size" => count($index_list)
                                ],
                                "aggs" => [
                                    "group_by_avg" => [
                                        "avg" => [
                                            "field" => "quote_last"
                                        ]
                                    ]
                                ]
                            ],
                            "sum_daily" => [
                                "sum_bucket" => [
                                    "buckets_path" => "group_by_asset>group_by_avg"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        if($period){
            $params['body']["query"]["bool"]["filter"][] =
                ["range" => ["quote_date"=> ["gte"=> "now-".$period."/d"]]];
        }
        $response = $client->search($params);
        $series = array();
        $baseline = $this->getIndexBaseline($index_list);
        $common_divisor = $this->getCommonDivisor($index_list);
        foreach ($response['aggregations']['group_by_date']['buckets'] as $bucket_date) {
            // Loop by date
            foreach ($bucket_date['group_by_asset']['buckets'] as $bucket_date_asset) {
                $baseline[$bucket_date_asset['key']]['quote_last'] = $bucket_date_asset['group_by_avg']['value'];
            }
            $sum_aggregations = 0;
            foreach ($baseline as $key=>$value) {
                $sum_aggregations += $value['quote_last'];
            }
            $element['name'] = $bucket_date['key_as_string'];
            $element['value'] = $sum_aggregations / $common_divisor;
            $series[] = $element;
        }
        $result = array();
        $multi['name'] = "index";
        $multi['series'] = $series;
        $result['multi'][] = $multi;

        return $result;
    }

    /**
     * Returns the financial index data for the specified index
     * @param $index : stock index name
     * @return mixed
     */
    public function getIndex($index_list, $interval, $period=0) {
        $hosts = Config::get ( 'elastic_config.connections.default.hosts' );
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_quote_*',
            'body' => [
                "size" => 0,
                "query" => [
                    "bool" => [
                        "filter" => [["terms"=> ["asset" => $index_list]]]
                    ]
                ],
                "aggs" => [
                    "group_by_date" => [
                        "date_histogram" => [
                            "field" => "quote_date",
                            "interval" => $interval,
                            "time_zone" => "America/New_York",
                            "min_doc_count" => 1
                        ],
                        "aggs" => [
                            "group_by_asset" => [
                                "terms" => [
                                    "field" => "asset",
                                    "size" => count($index_list)
                                ],
                                "aggs" => [
                                    "group_by_avg" => [
                                        "avg" => [
                                            "field" => "asset_capitalization_outstanding"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        if($period){
            $params['body']["query"]["bool"]["filter"][] =
                ["range" => ["quote_date"=> ["gte"=> "now-".$period."/d"]]];
        }
        $response = $client->search($params);

        //For each interval period : add the average price to the graph series while updating the baseline
        $series = array();
        $baseline = $this->getIndexBaseline($index_list, $period);
        $common_divisor = $this->getCommonDivisor($index_list);
        foreach ($response['aggregations']['group_by_date']['buckets'] as $bucket_period) {
            /* Build stock_array : associative CODE=>$rice for the period */
            $stock_array = [];
            foreach ($bucket_period['group_by_asset']['buckets'] as $bucket) {
                $stock_array[$bucket['key']] = $bucket['group_by_avg']['value'];
            }
            /* Update the baseline and Fill the gaps in the current period stock array*/
            foreach ($baseline as $key=>$value){
                /* If the baseline is more recent or the code does not exist in the array : update the array with the baseline*/
                if(!array_key_exists($key,$stock_array) || (strtotime($bucket_period['key_as_string']) < strtotime($baseline[$key]['quote_date']."+06:00"))){
                    $stock_array[$key] = $baseline[$key]['asset_capitalization_outstanding'];
                }
                /* If the baseline code exist in the stock array, and the baseline is older : update the baseline*/
                else {
                    $baseline[$key]['asset_capitalization_outstanding'] = $stock_array[$key];
                    $baseline[$key]['quote_date'] = $bucket_period['key_as_string'];
                }
            }
            /* Sum the period prices and divide with the total number of companies for this bucket period */
            $sum_aggregations = 0;
            foreach ($stock_array as $key=>$value) {
                $sum_aggregations += $value;
            }
            $element['name'] = $bucket_period['key_as_string'];
            $element['value'] = $sum_aggregations / $common_divisor;
            $series[] = $element;
        }
        $result = array();
        $multi['name'] = "index";
        $multi['series'] = $series;
        $result['multi'][] = $multi;
        return $result;
    }

    /**
     * Returns the financial index data for the specified index
     * @param $index : stock index name
     * @return mixed
     */
    public function getIndex3($index_list, $interval, $period=0) {
        $hosts = Config::get ( 'elastic_config.connections.default.hosts' );
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_quote_*',
            'body' => [
                "size" => 0,
                "query" => [
                    "bool" => [
                        "filter" => [["terms"=> ["asset" => $index_list]]]
                    ]
                ],
                "aggs" => [
                    "group_by_date" => [
                        "date_histogram" => [
                            "field" => "quote_date",
                            "interval" => $interval,
                            "time_zone" => "America/New_York",
                            "min_doc_count" => 1
                        ],
                        "aggs" => [
                            "group_by_asset" => [
                                "terms" => [
                                    "field" => "asset",
                                    "size" => count($index_list)
                                ],
                                "aggs" => [
                                    "group_by_avg" => [
                                        "avg" => [
                                            "field" => "quote_last"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        if($period){
            $params['body']["query"]["bool"]["filter"][] =
                ["range" => ["quote_date"=> ["gte"=> "now-".$period."/d"]]];
        }
        $response = $client->search($params);

        //For each interval period : add the average price to the graph series while updating the baseline
        $series = array();
        $baseline = $this->getIndexBaseline($index_list, $period);
        foreach ($response['aggregations']['group_by_date']['buckets'] as $bucket_period) {
            /* Build stock_array : associative CODE=>$rice for the period */
            $stock_array = [];
            foreach ($bucket_period['group_by_asset']['buckets'] as $bucket) {
                $stock_array[$bucket['key']] = $bucket['group_by_avg']['value'];
            }
            /* Update the baseline and Fill the gaps in the current period stock array*/
            foreach ($baseline as $key=>$value){
                /* If the baseline is more recent or the code does not exist in the array : update the array with the baseline*/
                if(!array_key_exists($key,$stock_array) || (strtotime($bucket_period['key_as_string']) < strtotime($baseline[$key]['quote_date']."+06:00"))){
                    $stock_array[$key] = $baseline[$key]['quote_last'];
                }
                /* If the baseline code exist in the stock array, and the baseline is older : update the baseline*/
                else {
                    $baseline[$key]['quote_last'] = $stock_array[$key];
                    $baseline[$key]['quote_date'] = $bucket_period['key_as_string'];
                }
            }
            /* Sum the period prices and divide with the total number of companies for this bucket period */
            $sum_aggregations = 0;
            foreach ($stock_array as $key=>$value) {
                $sum_aggregations += $value;
            }
            $element['name'] = $bucket_period['key_as_string'];
            $element['value'] = 100 * $sum_aggregations / count($stock_array);
            $series[] = $element;
        }
        $result = array();
        $multi['name'] = "index";
        $multi['series'] = $series;
        $result['multi'][] = $multi;
        return $result;
    }

    /**
     * Returns the financial index summary (index, change, change%) for the specified index
     * @param $index : stock index name
     * @return mixed
     */
    public function getSectorSummary2($index_list) {
        $hosts = Config::get('elastic_config.connections.default.hosts');
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_quote_*',
            'body' => [
                "size" => count($index_list),
                "query" => [
                    "bool" => [
                        "filter" => [
                            "terms" => [
                                "asset" => $index_list
                            ]
                        ]
                    ]
                ],
                "aggs" => [
                    "group_docs" => [
                        "top_hits" => [
                            "size" => 1,
                            "sort" => [["quote_date" => ["order" => "desc"]]]
                        ]
                    ]
                ],
                "_source" => ["asset", "quote_date", "quote_last", "quote_volume", "quote_last", "quote_open",
                    "quote_high", "quote_low", "quote_last_close", "asset_capitalization_outstanding"]
            ]
        ];
        $response = $client->search($params);
        $index_value_now = 0;
        $index_value_last_close = 0;
        $common_divisor = $this->getCommonDivisor($index_list);
        foreach ($response['hits']['hits'] as $bucket_hit) {
            $index_value_now += $bucket_hit["_source"]['quote_last'];
            $index_value_last_close += $bucket_hit["_source"]['quote_last_close'];
        }
        /* TODO remove this line !!!*/  $index_value = $index_value_last_close / $common_divisor;
        $index_value_now /= $common_divisor;
        $index_value_last_close /= $common_divisor;
        $difference = $index_value_last_close - $index_value_now;
        $difference_pct = 100 * $difference / $index_value_last_close;
        $caret = ($difference > 0) ? "up" : "down";
        /* TODO remove this line !!!*/  $index_value_now = $index_value;

        return ["caret" => $caret, "difference" => $difference, "index_value_now" => $index_value_now,
            "difference_pct" => $difference_pct, "index_list" => implode(" ",$index_list)];
    }

    /**
     * Returns the financial index summary (index, change, change%) for the specified index
     * @param $index : stock index name
     * @return mixed
     */
    public function getSectorSummary($index_list) {
        $hosts = Config::get('elastic_config.connections.default.hosts');
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_quote_*',
            'body' => [
                "size" => 0,
                "query" => [
                    "bool" => [
                        "filter" => [
                            "terms" => [
                                "asset" => $index_list
                            ]
                        ]
                    ]
                ],
                "aggs" => [
                    "group_by_asset"=> [
                        "terms"=> [
                            "field"=> "asset",
                            "size" => count($index_list)
                        ],
                        "aggs" => [
                            "group_docs" => [
                                "top_hits" => [
                                    "size" => 1,
                                    "sort" => [["quote_date" => ["order" => "desc"]]],
                                    "_source" => ["asset", "quote_date", "quote_last", "quote_last_close", "asset_capitalization_outstanding"]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $response = $client->search($params);

        $index_value_now = 0;
        $index_value_last_close = 0;
        foreach ($response['aggregations']['group_by_asset']['buckets'] as $bucket_hit) {
            $element = $bucket_hit['group_docs']['hits']['hits'][0]['_source'];
            if($element['quote_last'] > 0) {
                $index_value_now += $element['asset_capitalization_outstanding'];
                $asset_outstanding_shares = $element['asset_capitalization_outstanding'] / $element['quote_last'];
                $index_value_last_close += $element['quote_last_close'] * $asset_outstanding_shares;
            }
        }
        $common_divisor = $this->getCommonDivisor($index_list);
        $index_value_now /= $common_divisor;
        $index_value_last_close /= $common_divisor;
        $difference = $index_value_now - $index_value_last_close;
        $difference_pct = 100 * $difference / $index_value_last_close;
        $caret = ($difference > 0) ? "up" : "down";

        return ["caret" => $caret, "difference" => $difference, "index_value_now" => $index_value_now,
            "difference_pct" => $difference_pct, "index_list" => implode(" ",$index_list)];
    }

    /**
     * Returns the list of the top/bottom movers from the stocks in the index
     * @param $index : stock index name (see custom_config for the relative stocks)
     * @return mixed : 2 arrays = 5 top movers and 5 bottom movers
     */
    public function getTopBottomMovers($index_list) {
        $hosts = Config::get('elastic_config.connections.default.hosts');
        $client = ClientBuilder::create()->setHosts($hosts)->build();
        $params = [
            'index' => 'ticker_quote_*',
            'body' => [
                "size" => 0,
                "query" => [
                        "bool" => [
                            "filter" => [
                                "terms" => [
                                    "asset" => $index_list
                            ]
                        ]
                    ]
                ],
                "aggs" => [
                        "group" => [
                            "terms" => [
                                "size" => 1000,
                            "field" => "asset"
                        ],
                        "aggs" => [
                                "group_docs" => [
                                    "top_hits" => [
                                        "size" => 1,
                                    "sort" => [
                                        ["quote_date" => ["order" => "desc"]]
                                    ],
                                    "_source" => ["asset", "quote_date", "quote_last", "quote_volume_3m_average",
                                        "quote_volume", "create_at", "quote_last_close", "asset_name"]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $response = $client->search($params);
        foreach ($response['aggregations']['group']['buckets'] as $bucket_hit) {
            $bucket = $bucket_hit['group_docs']['hits']['hits'][0]['_source'];
            $bucket['change'] = $bucket['quote_last'] - $bucket['quote_last_close'];
            if($bucket['quote_last_close'] > 0) {
                $bucket['change_pct'] = 100 * $bucket['change'] / $bucket['quote_last_close'];
            } else {
                $bucket['change_pct'] = null;
            }
            $bucket['caret'] = ($bucket['change'] > 0) ? "up" : "down";
            $series[] = $bucket;
        }
        usort($series, function($a, $b) {
            return $a['change'] <=> $b['change'];
        });
        $top_movers = array_slice($series,-5,5);
        usort($top_movers, function($a, $b) {
            return $b['change_pct'] <=> $a['change_pct'];
        });
        $bottom_movers = array_slice($series,0,5);
        usort($bottom_movers, function($a, $b) {
            return $a['change_pct'] <=> $b['change_pct'];
        });
        return ["top" => $top_movers, "bottom" => $bottom_movers];
    }

    public function getDailyTickerData() {
        return $this->model
            ->select('ticker.*')
            ->selectRaw('ROUND(ticker.last - ticker.open , 2) as price_change')
            ->selectRaw("ROUND(((ticker.last - ticker.open)/ticker.last)*100 ,2) as change_percentage")
            ->selectRaw("IF (ticker.last > ticker.open , 1,0) as change_up")
            ->whereNotNull('last')
            ->whereNotNull('open')
            ->orderBy('symbol')
            ->get();
    }
    public function getSectorFromTicker($ticker) {
        return $this->model
            ->select('ticker.sector')
            ->where('ticker.symbol', "=", $ticker)
            ->get()[0]->sector;
    }
    public function getAllTickersFromSector($sector) {
        $tickers = [];
        $results = $this->model
            ->select('ticker.symbol')
            ->where('ticker.sector', "=", $sector)
            ->get();
        foreach ($results as $result) {
            $tickers[] = $result->symbol;
        }
        return $tickers;
    }
    public function getAllSectors() {
        $sectors = [];
        $results = $this->model
            ->distinct()
            ->get(['sector']);
        foreach ($results as $result) {
            if($result->sector) $sectors[] = $result->sector;
        }
        return $sectors;
    }
    public function getAllTickersFromIndex($index) {
        $index = strtolower($index);
        $tickers = [];
        $results = $this->model
            ->select('ticker.symbol')
            ->where('ticker.index_'.$index, "=", 1)
            ->get();
        foreach ($results as $result) {
            $tickers[] = $result->symbol;
        }
        return $tickers;
    }
    public function getAllTickers() {
        $tickers = [];
        $results = $this->model
            ->select('ticker.symbol')
            ->get();
        foreach ($results as $result) {
            $tickers[] = $result->symbol;
        }
        return $tickers;
    }

    /**
     * get all companies list from ticker table
     * @return array
     */
    public function getAllCompanies() {
        $tickers = [];
        $results = $this->all(['ticker.id', 'ticker.symbol', 'ticker.company']);

        foreach ($results as $result) {
            $tickers[] = [
                'id' => $result->id,
                'itemName' => $result->symbol . '-' . $result->company,
                'itemCode' => $result->symbol
            ];
        }
        return $tickers;
    }

    /**
     * get companies by ids
     * @return array
     */
    public function getCompaniesByIds($idArr = array()) {
        
        $idArr = array_column($idArr, 'id');
        $tickers = [];
        $results = $this->model
            ->select('ticker.symbol')
            ->WhereIn("ticker.id", $idArr)
            ->get();
        foreach ($results as $result) {
            $tickers[] = $result->symbol;
        }
        
        return $tickers;
    }
}