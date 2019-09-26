<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\WooCommerceService;
use App\Traits\CompanyProfileValidators;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserStripeRepository;
use App\Repositories\ReportPurchaseRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Events\EventWooCoomerceOrder;
use App\Models\User;

class PaymentController extends ApiController
{

    use CompanyProfileValidators;

    private $error = 'error';
    private $message = 'message';

    /**
     * @var UserService
     */
    private $paymentService;
    private $wooCommerceService;
    private $userStripeRepository;
    private $reportPurchaseRepository;
    private $userRepository;

    /**
     * PaymentController constructor.
     * @param UserService $userService
     */
    public function __construct(PaymentService $paymentService, WooCommerceService $wooCommerceService, UserStripeRepository $userStripeRepository, ReportPurchaseRepository $reportPurchaseRepository, UserRepository $userRepository)
    {
        $this->paymentService = $paymentService;
        $this->wooCommerceService = $wooCommerceService;
        $this->userStripeRepository = $userStripeRepository;
        $this->reportPurchaseRepository = $reportPurchaseRepository;
        $this->userRepository = $userRepository;
    }

    public function getCardList(Request $request)
    {
        $objPaymentCardList = $this->paymentService->getCustomerCardDetails ( Auth::user ()->email );
        return response ()->json ( ['data' => $objPaymentCardList], 200 );
    }

    public function getSourceList(Request $request)
    {
        $objPaymentCardList = $this->paymentService->getCustomerSourcetails ( Auth::user ()->email );
        return response ()->json ( ['data' => $objPaymentCardList], 200 );
    }

    public function addPaymentProcess(Request $request)
    {
        $coupon_discount = 0;
        $coupon_id = 0;
        $request_info = $request->all ();
        $coupon_id =  isset($request_info[ 'coupon_id' ]) ?$request_info[ 'coupon_id' ] : '';
        $coupon_code=  isset($request_info[ 'coupon_code' ]) ?$request_info[ 'coupon_code' ] : 0;
        $coupon_discount=  isset($request_info[ 'discount' ]) ?$request_info[ 'discount' ] : 0;
        $coupn_prod_woo_id = isset($request_info[ 'coupn_prod_woo_id' ]) ?$request_info[ 'coupn_prod_woo_id' ] : [];
        $coupon_id_chk = false;

        if(!empty($coupon_id)){

            $coupon_id_chk = $this->wooCommerceService->retrieveWooData ( 'coupons/' . $coupon_id );

            if(!isset($coupon_id_chk->id)){
                return response ()->json ( ['error' => 'coupon code is not valid'], 400 );
            }
            $coupon_id_chk = true;
        }

        if(!empty($coupon_discount)){

            if($coupon_id_chk == false){
                return response ()->json ( ['error' => 'coupon code is not valid'], 400 );
            }

        }

        $objStripeTblId = null;
        $objPaymentProcess[ 'code' ] = null;
        $objPaymentProcess[ 'transaction_id' ] = null;
        $objPaymentProcess[ 'stripe_details_id' ] = null;
        $userInformation = $this->userRepository->basicInfoById ( Auth::user ()->id );
        $objPaymentProcess = $this->paymentService->primaryPaymentProcess ($request_info );

        if ( !empty( $objPaymentProcess ) ) {

            if ( $objPaymentProcess[ 'code' ] == 400 ) {
                return response ()->json ( ['error' => $objPaymentProcess[ 'message' ]], 400 );
            }
            if ( $objPaymentProcess[ 'code' ] == 200 ) {
                $user = User::find ( Auth::user ()->id );
                $user->reports_purchased = 'y';
                $user->save();
                $objPaymentProcess[ 'stripe_details_id' ] = $this->userStripeRepository->addUserStripe ( $request_info, $objPaymentProcess[ 'code' ], $objPaymentProcess[ 'transaction_id' ] );
                if ( !empty( $objPaymentProcess[ 'stripe_details_id' ] ) ) {
                    $this->reportPurchaseRepository->addReportPurchase ( $request_info, $objPaymentProcess[ 'stripe_details_id' ] );
                    \Event::fire ( new EventWooCoomerceOrder( $userInformation,
                        $request[ 'product' ],
                        $objPaymentProcess[ 'stripe_details_id' ],
                        $this->userStripeRepository,
                        $coupon_code ,
                        $coupon_discount ,
                        $coupon_id,
                        $coupn_prod_woo_id, $objPaymentProcess[ 'stripe_order_id' ] ) );
                    return response ()->json ( ['data' => $objPaymentProcess[ 'status' ]], 200 );

                } else {
                    return response ()->json ( ['error' => 'Database Tranaction Failed'], 400 );
                }

            }

        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }


    public function subscriptionList()
    {
        $productList = $this->wooCommerceService->getSpecificWooData ( 'products', ['status' => 'publish', 'type' => 'variable-subscription', 'per_page' => '100'],
            ['id' => '', 'name' => '', 'status' => '', 'type' => '', 'description' => '', 'price' => '', 'price_html' => ''] );

        if ( !empty( $productList ) ) {
            return $this->respond ( $productList );
        } else {
            return null;
        }
    }







}




