<?php

namespace App\Http\Controllers;

use App\Equio\Helper;
use App\Models\Blog;
use App\Models\Cannaclips;
use App\Models\Chart;
use App\Models\Profiles;
use App\Models\Report;
use App\Models\TopFive;
use App\Models\Webinar;
use App\Services\SearchService;
use App\Traits\SearchValidators;
use Illuminate\Http\Request;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use App\Services\CmsService;
use  App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Equio\Exceptions\EquioException;
use Illuminate\Support\Facades\Config;
use App\Repositories\SubscriptionRepository;

class SearchController extends ApiController
{


    private $cmsService;
    private $routeName;
    private $subscriptions;
    private $error = 'error';
    private $message = 'message';

    /**
     * @var UserService
     */
    private $searchService;

    /**
     * UsersController constructor.
     * @param UserService $userService
     */
    public function __construct(SearchService $searchService, CmsService $cmsService, SubscriptionRepository $subscription)
    {
        $this->searchService = $searchService;
        $this->subscriptions = $subscription;
        $this->cmsService = $cmsService;
        $this->woocommerce = new Client(
            Config::get ( 'custom_config.WOOCOMMERCE_API_URL' ),
            Config::get ( 'custom_config.WOOCOMMERCE_API_KEY' ),
            Config::get ( 'custom_config.WOOCOMMERCE_API_SECRET' ),
            ['wp_api' => true, 'version' => 'wc/v1',]
        );
    }

    public function inSightDailyInfo(Request $request)
    {
        $requestData = $request->all ();
        $paginate = $this->perPage = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '15';
        $search = !empty( $requestData[ 'search' ] ) ? $request[ 'search' ] : '';
        $status = $this->accessStatus ( $request );
        $data = $this->searchService->searchByScore ( new TopFive(), $search, $paginate, $status );

        if ( !empty( $search ) && !empty( $data ) ) {
            if ( empty( $data[ 0 ] ) ) {
                return response ()->json ( ['data' => null], 200 );
            }
            return response ()->json ( ['data' => $data], 200 );
        } elseif ( empty( $search ) ) {

            return response ()->json ( ['data' => null], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );


    }

    public function blogInfo(Request $request)
    {
        $requestData = $request->all ();
        $paginate = $this->perPage = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '15';
        $search = !empty( $requestData[ 'search' ] ) ? $request[ 'search' ] : '';
        $status = $this->accessStatus ( $request );
        $data = $this->searchService->searchByScore ( new Blog(), $search, $paginate, $status );
        if ( !empty( $search ) && !empty( $data ) ) {
            if ( empty( $data[ 0 ] ) ) {
                return response ()->json ( ['data' => null], 200 );
            }
            return response ()->json ( ['data' => $data], 200 );

        } elseif ( empty( $search ) ) {

            return response ()->json ( ['data' => null], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function chartInfo(Request $request)
    {
        $requestData = $request->all ();
        $paginate = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '15';
        $search = !empty( $requestData[ 'search' ] ) ? $request[ 'search' ] : '';
        $data = $this->searchService->searchByScore ( new Chart(), $search, $paginate );
        if ( !empty( $search ) && !empty( $data ) ) {
            if ( empty( $data[ 0 ] ) ) {
                return response ()->json ( ['data' => null], 200 );
            }
            return response ()->json ( ['data' => $data], 200 );

        } elseif ( empty( $search ) ) {

            return response ()->json ( ['data' => null], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function accessStatus($request)
    {

        $helper = new Helper();
        $this->routeName = $request->getRequestUri ();
        $status_response = $helper->checkTrailPeriodUser ( Auth::user ()->id, $this->routeName, $this->subscriptions );
        if ( !empty( $status_response[ 'status' ] ) ) {
            $status = true;
        } else {
            $status = false;
        }
        return $status;
    }


    public function profileInfo(Request $request)
    {
        $requestData = $request->all ();
        $status = $this->accessStatus ( $request );


        $paginate = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '15';
        $search = !empty( $requestData[ 'search' ] ) ? $request[ 'search' ] : '';
        $data = $this->searchService->searchByScore ( new Profiles(), $search, $paginate, $status );

        if ( !empty( $search ) && !empty( $data ) ) {
            if ( empty( $data[ 0 ] ) ) {
                return response ()->json ( ['data' => null], 200 );
            }
            return response ()->json ( ['data' => $data], 200 );
        } elseif ( empty( $search ) ) {

            return response ()->json ( ['data' => null], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }


    public function reportInfo(Request $request)
    {

        $requestData = $request->all ();
        $paginate = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '15';
        $search = !empty( $requestData[ 'search' ] ) ? $request[ 'search' ] : '';
        $data = $this->searchService->searchByScore ( new Report(), $search, $paginate );

        if ( !empty( $search ) && !empty( $data ) ) {

            if ( empty( $data[ 0 ] ) ) {
                return response ()->json ( ['data' => null], 200 );
            }
            return response ()->json ( ['data' => $data], 200 );
        } elseif ( empty( $search ) ) {

            return response ()->json ( ['data' => null], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }


    public function videoInfo(Request $request)
    {
        $requestData = $request->all ();
        $paginate = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '15';
        $search = !empty( $requestData[ 'search' ] ) ? $request[ 'search' ] : '';
        $data = $this->searchService->searchVideoByScore ( new Webinar(), new Cannaclips(), $search, $paginate );

        if ( !empty( $search ) && !empty( $data ) ) {
            if ( empty( count ( $data ) ) ) {
                return response ()->json ( ['data' => null], 200 );
            }
            return response ()->json ( ['data' => $data], 200 );
        } elseif ( empty( $search ) ) {
            return response ()->json ( ['data' => null], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function chartSearchAccess(Request $request)
    {
        $access = Helper::checkChartSearchAccess ();
        if ( ($access == true) ) {

            return response ()->json ( ['data' => true], 200 );
        } else if ( ($access == false) ) {

            return response ()->json ( ['Error' => "Not authorized.", 'status_code' => 1], 403 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function videoSearchAccess(Request $request)
    {

        $requestData = $request->all ();
        if ( !empty( $requestData[ 'videotable' ] ) ) {

            if ( $requestData[ 'videotable' ] == "cannaclips" ) {
                return response ()->json ( ['data' => true], 200 );
            } else if ( $requestData[ 'videotable' ] == "webinars" ) {

                $access = Helper::checkWebniarSearchAccess ();
                if ( ($access == true) ) {

                    return response ()->json ( ['data' => true], 200 );
                } else if ( ($access == false) ) {

                    return response ()->json ( ['Error' => "Not authorized.", 'status_code' => 1], 403 );
                }

            }
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function reportSearchAccess(Request $request)
    {
        $user = Auth::user ();
        $helper = new Helper();
        $requestData = $request->all ();
        $reportId = !empty( $requestData[ 'report_id' ] ) ? $requestData[ 'report_id' ] : '';
        $reportAvaiable = $this->searchService->checkReportAvailble ( $reportId );
        $reportExcempt = $this->searchService->checkReportExcempt ( $reportId );


        if ( !empty( $reportAvaiable ) ) {

            if ( $helper->checkReportSearchAccess () == true ) {
                if ( $reportAvaiable != 1 ) {
                    return response ()->json ( ['Error' => "Not authorized.", 'status_code' => 1], 403 );
                }
            }
        }
        if ( $helper->checkReportSearchAccess () == true ) {


            if($reportExcempt == 1){
                return response ()->json ( ['data' => true], 200 );
            } else {

                $purchasedOrder = $this->getOrderDetails ( $user->email );
                $allReportsData = $this->cmsService->getPurchasedReportEssentail ( array_unique ( $purchasedOrder->toArray () ) );
                if ( in_array ( $reportId, $allReportsData ) ) {

                    return response ()->json ( ['data' => true], 200 );
                }
                return response ()->json ( ['Error' => "You have not purchased this report. To purchase this report and add it to your My Reports section (and be accessible in search results), please Click here.", 'status_code' => 1], 403 );


            }

        }

        $this->routeName = $request->getRequestUri ();
        $status = $helper->checkTrailPeriodUser ( Auth::user ()->id, $this->routeName, $this->subscriptions );
        $reportId = !empty( $this->cmsService->essentailReportDetail ( $reportId )->id ) ? $this->cmsService->essentailReportDetail ( $reportId )->id : '';

        if ( $this->cmsService->getPriceZeroReportName ( $reportId ) ) {

            return response ()->json ( ['data' => true], 200 );
        }
        $purchasedOrder = $this->getOrderDetails ( $user->email );

        $allReportsData = $this->cmsService->getPurchasedReportEssentail ( array_unique ( $purchasedOrder->toArray () ) );
        if ( in_array ( $reportId, $allReportsData ) ) {

            return response ()->json ( ['data' => true], 200 );
        }
        return response ()->json ( ['Error' => "Not authorized.", 'status_code' => 1], 403 );

    }


    public function profileSearchAccess(Request $request)
    {
        return response ()->json ( ['data' => true], 200 );
    }


    /**
     * @param $email
     * @return Collection
     */
    public function getOrderDetails($email)
    {
        try {
            $purchasedItem = collect ();
            $results = $this->woocommerce->get (
                'orders',
                [
                    'search' => $email,
                    'per_page' => 50,
                    'status' => 'completed'
                ]
            );
            foreach ( $results as $result ) {
                foreach ( $result->line_items as $lineItem ) {
                    $purchasedItem->push ( $lineItem->product_id );
                }
            }
            $results = $this->woocommerce->get (
                'orders',
                [
                    'search' => $email,
                    'per_page' => 50,
                    'status' => 'processing'
                ]
            );
            foreach ( $results as $result ) {
                foreach ( $result->line_items as $lineItem ) {
                    $purchasedItem->push ( $lineItem->product_id );
                }
            }
            return $purchasedItem;

        } catch (HttpClientException $e) {
            throw new EquioException( $e->getMessage () );
        } catch (\Exception $e) {
            throw new EquioException( $e->getMessage () );
        }
    }


}




