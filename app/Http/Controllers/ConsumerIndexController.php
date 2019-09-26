<?php

namespace App\Http\Controllers;

use App\Services\ConsumerIndexService;
use Illuminate\Http\Request;
use App\Equio\Helper;
use Illuminate\Support\Facades\Config;


class ConsumerIndexController extends ApiController
{

    private $error = 'error';
    private $message = 'message';

    /**
     * @var ConsumerIndexService
     */
    private $consumerIndexServiceService;

    /**
     * consumerIndexController constructor.
     * @param ConsumerIndexServiceService
     */
    public function __construct(ConsumerIndexService $consumerIndexServiceService) {
        $this->consumerIndexServiceService = $consumerIndexServiceService;
    }

    public function retailPriceDetail(Request $request) {
        $data = $this->consumerIndexServiceService->getZeferRetailPriceDetails ( $request->all () );
        if ( $data ) {
            return response ()->json ( ['data' => $data,
            ], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function getStateProductDetail(Request $request) {
        $data = $this->consumerIndexServiceService->getStateProductInfo ( $request->all () );
        if ( $data ) {
            return response ()->json ( ['data' => $data,
            ], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function consumerGroupDetail(Request $request) {
        $data = $this->consumerIndexServiceService->getConsumerGroupDetails ( $request->all () );
        usort($data['state_data'], function ($a, $b) {
            return -(
                $a['segment_data']['female_pop']+$a['segment_data']['male_pop']
                <=>
                $b['segment_data']['female_pop']+$b['segment_data']['male_pop']
            );
        });
        if ( $data ) {
            return response ()->json ( ['data' => $data,
            ], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }


}

