<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\WooCommerceService;
use App\Traits\CompanyProfileValidators;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class WoocommerceController extends ApiController
{

    private $error = 'error';
    private $message = 'message';

    /**
     * @var WoocommerceService
     */
    private $wooCommerceService;


    /**
     * PaymentController constructor.
     * @param UserService $userService
     */
    public function __construct( WooCommerceService $wooCommerceService)
    {

        $this->wooCommerceService = $wooCommerceService;
    }

    public function getBundleDetail(Request $request)
    {
        $requestData = $request->all ();
        $productId = (!empty( $request[ 'id' ] )) ? ($request[ 'id' ]) : 0;
        $productList = $this->wooCommerceService->getBundleDetail( $productId);
         if ( $productList ) {
            return response ()->json ( ['data' => $productList], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }


    public function getSubScriptionLevel(Request $request)
    {
        $requestData = $request->all ();
        $productId = (!empty( $request[ 'id' ] )) ? ($request[ 'id' ]) : 0;
        $productList = $this->wooCommerceService->getBundleDetail( $productId);
        if ( $productList ) {
            return response ()->json ( ['data' => $productList], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }


}






