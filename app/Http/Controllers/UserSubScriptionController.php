<?php

namespace App\Http\Controllers;

use App\Equio\Helper;
use App\Services\ModuleSubscriptionTrackerService;
use Illuminate\Http\Request;
use App\Repositories\UserStripeRepository;
use App\Repositories\UserRepository;
use App\Services\WooCommerceService;
use App\Services\PaymentService;
use Join;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Automattic\WooCommerce\Client;
use App\Equio\Exceptions\EquioException;
use App\Services\ShortpositionActivityLogService;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;


/**
 * Class UserSubScriptionController
 * @package App\Http\Controllers
 */
class UserSubScriptionController extends Controller {
    private $userStripeRepository;
    private $shortpositionActivityLogService;
    private $_moduleSubscriptionTrackerService;
    private $paymentService;
    private $userRepository;
    private $error = 'error';

    /**
     * UserSubScriptionController constructor.
     * @param WooCommerceService $wooCommerceService
     * @param PaymentService $paymentService
     * @param UserRepository $userRepository
     * @param UserStripeRepository $userStripeRepository
     * @param ShortpositionActivityLogService $shortpositionActivityLogService
     * @param ModuleSubscriptionTrackerService $moduleSubscriptionTrackerService
     */
    public function __construct(
        WooCommerceService $wooCommerceService,
        PaymentService $paymentService,
        UserRepository $userRepository,
        UserStripeRepository $userStripeRepository,
        ShortpositionActivityLogService $shortpositionActivityLogService,
        ModuleSubscriptionTrackerService $moduleSubscriptionTrackerService
    ) {
        $this->paymentService = $paymentService;
        $this->wooCommerceService = $wooCommerceService;
        $this->userRepository = $userRepository;
        $this->userStripeRepository = $userStripeRepository;
        $this->shortpositionActivityLogService = $shortpositionActivityLogService;
        $this->_moduleSubscriptionTrackerService = $moduleSubscriptionTrackerService;
    }

    /**
     * retrive woocommerce subcription plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubscriptionPlan() {
        $categoryId = Config::get('custom_config.WOOCOMMERCE_CATEGORY_ID');
        if (empty($categoryId)) {
            return response()->json(
                ['error' => __('woocommerce category ID not set')
                ],
                400
            );
        }
        $data = $this->wooCommerceService->getSubscriptionPlan();
        \Log::info("===== data ", ['subscription_package' => $data]);

        if ($data) {
            return response()->json(['data' => $data], 200);
        }

        return response()->json(
            ['error' => __('messages.un_processable_request')
            ],
            400
        );
    }

    public function recurringDate($date, $year = null) {
        $start = new \DateTime($date);
        $end = clone $start;
        $duration = !empty($year) ? $year : 1;
        $end->modify('+' . $duration . ' month');

        if ($year == null) {

            while (($start->format('m') + 1) != $end->format('m')) {
                $end->modify('-1 day');
            }

        } else {
            $end->modify('-1 day');
        }
        return $end->format('Y-m-d H:i:s');
    }

    /**
     * Woocommerce subcription payment process
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addPaymentProcess(Request $request)
    {

        $objPaymentProcess['code'] = null;
        $objPaymentProcess['transaction_id'] = null;
        $requests = $request->all();
        $objPaymentProcess = $this->paymentService->customrSoruceCreateProcess(
            $requests
        );

        if (!empty($objPaymentProcess)) {

            if ($objPaymentProcess['code'] == 400) {
                return response()->json(
                    ['error' => $objPaymentProcess['message']
                    ],
                    400
                );
            }
            if ($objPaymentProcess['code'] == 200) {
                $objPaymentProcess['stripe_details_id'] = $this->userStripeRepository
                    ->addUserStripe(
                        $requests,
                        $objPaymentProcess['code'],
                        $objPaymentProcess['transaction_id']
                    );
                if (!empty($objPaymentProcess['stripe_details_id'])) {
                    $this->userRepository->updateUserPaidSubscription(
                        !empty($requests['type']) ? $requests['type'] : null,
                        !empty($requests['name']) ? $requests['name'] : null
                    );
                    $objCreateSubscription = $this->createSubscription(
                        $requests['id'],
                        $requests['type'],
                        $objPaymentProcess['stripe_details_id'],
                        $objPaymentProcess['stripe_order_id']
                    );
                    if (isset($objCreateSubscription['message'])) {
                        return response()->json(
                            ['error' => $objCreateSubscription['message']
                            ],
                            400
                        );
                    }
                    return response()->json(
                        [
                        'data' => $objPaymentProcess['status']
                         ],
                        200
                    );

                } else {
                    return response()->json(
                        ['error' => 'Database Tranaction Failed'
                        ],
                        400
                    );
                }

            }

        }
        return response()->json(
            [
                'error' => __('messages.un_processable_request')
            ],
            400
        );
    }

    /**
     * create Woocommerce Customr
     * @param $userInformation
     * @return int
     * @throws EquioException
     */
    public function createWooCustomer($userInformation)
    {
        $customerId= 0;
        try {

            $data = array(
               'email' => $userInformation['email'],
               'first_name' => $userInformation['first_name'],
               'last_name' => $userInformation['last_name'],
               'username' =>
                   $userInformation['first_name'].  $userInformation['last_name'],
               'billing_address' =>
                   array(
                       'first_name' => $userInformation['first_name'],
                       'last_name' => $userInformation['last_name'],
                       'address_1' => $userInformation['street_address1'],
                       'address_2' => $userInformation['street_address2'],
                       'city' => $userInformation['city'],
                       'state' => $userInformation['state'],
                       'postcode' => "" . $userInformation['zip'] . "",
                       'country' => $userInformation['country'],
                       'email' => $userInformation['email'],
                       'phone' => $userInformation['phone_number'],
                   )
             );

             $customerCreate = $this->storeJsonWooData('customers', $data);
             \Log::info("===== Customer Create", ['data' => $customerCreate]);

             if(!empty($customerCreate)) {

                 $customerId = $customerCreate->id;

          }
           return $customerId;

       }
        catch (HttpClientException $e) {
        return  $e->getMessage ();
       } catch
       (\Exception $e) {

            return  $e->getMessage ();
       }

    }

    /**
     * create subcription of woocommerce
     * @param int $product_id
     * @param null $billing_type
     * @param null $stripePaymentProcessId
     * @return bool
     */
    public function createSubscription(
        $productId = 0,
        $billingType = null,
        $stripePaymentProcessId = null,
        $strpeOrderId = null
    ) {

        $userInformation = $this->userRepository->basicInfoById(Auth::user()->id);
        $stipeObj = $this->paymentService
            ->getCustomerDefaultCardDetails(Auth::user()->email);
        $customerId = $this->wooCommerceService->getCustomerId();

        if (empty($customerId)) {

            $customerId = $this->createWooCustomer($userInformation);
            if(gettype($customerId) == "string"){
                return [
                    'flag' => false,
                    'message' => $customerId
                ];

            }

        }

        $billing_period = null;

        if ($billingType == "Monthly" ||  $billingType ==  "Monthly Billing") {
            $billing_period = "month";
            $billingInterval = 1;
            $next_payment_date = $this->recurringDate(date('Y-m-d H:i:s'));

        }

        if ($billingType == "Annual" ||  $billingType ==  "Annual Billing") {
            $billing_period = "year";
            $billingInterval = 2;
            $next_payment_date = $this->recurringDate(date('Y-m-d H:i:s'), 12);
        }

        $startTime = strtotime("+0 minutes", strtotime(gmdate("Y-m-d H:i:s")));
        $startTime = date("Y-m-d H:i:s", $startTime);

        $data = array(
            'customer_id' => $customerId,
            'status' => 'active',
            'billing_period' => $billing_period,
            'billing_interval' => $billingInterval,
            'start_date' => $startTime,
            'next_payment_date' => $next_payment_date,
            'payment_method' => 'stripe',
            'payment_details' =>
                array(
                    'post_meta' =>
                        array(
                            '_stripe_customer_id' => $stipeObj['customer'],
                            '_stripe_card_id' => $stipeObj['card_id'],
                        ),
                ),
            'billing_address' =>
                array(
                    'first_name' => $userInformation['first_name'],
                    'last_name' => $userInformation['last_name'],
                    'address_1' => $userInformation['street_address1'],
                    'address_2' => $userInformation['street_address2'],
                    'city' => $userInformation['city'],
                    'state' => $userInformation['state'],
                    'postcode' => "" . $userInformation['zip'] . "",
                    'country' => $userInformation['country'],
                    'email' => $userInformation['email'],
                    'phone' => $userInformation['phone_number'],
                ),

            'line_items' =>
                array(
                    0 =>
                        array(
                            'product_id' => $productId,
                            'quantity' => 1,
                        ),
                ),
        );

        $order = $this->storeJsonWooData('subscriptions', $data);
        \Log::info("===== Subscription", ['order' => $order]);

        if (empty($order)) {
            return [
                'flag' => false,
                'message' => Auth::user()->email . "Create Subscription Fail"
            ];

        } else {

            $paid_subscription_start = date('Y-m-d H:i:s', strtotime($order->start_date));
            $paid_subscription_end = date('Y-m-d H:i:s', strtotime($order->next_payment_date));
            $trial = 1;
            $subscription_level = Helper::stringContain(strstr($order->line_items[0]->name, '-', true));
            if(empty($subscription_level))
            {
                $subscription_level = Helper::stringContain(($order->line_items[0]->name));
            }

            $this->userStripeRepository->updateUserStripe($stripePaymentProcessId, true);
            DB::table("users")
                ->where('id', Auth::user()->id)
                ->update(['paid_subscription_start' => $paid_subscription_start,
                    'paid_subscription_end' => $paid_subscription_end,
                    'subscription_level' => $subscription_level,
                    'trial' => $trial,
                    'subscription_renewal' => 'y',
                    'no_renewal_reason' => ''
                ]);

            $description = '';
            if (isset($order->line_items[0]->name) && isset($order->id)) {
                $description = "WooOrderId-" . $order->id . "," . $productId . "-" . $order->line_items[0]->name;
                if (isset($strpe_order_id)) {
                    $this->paymentService->setChargeDescription($strpeOrderId, $description, Config::get('custom_config.Payment_TYPE')[2]);
                }

            }

            return true;
        }
    }

    /**
     * cancel stripe subcription
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function cancelStripeSubcription(Request $request) {

        \Log::info("==== get stripe cancel  in controller ", ['u' => json_encode($request)]);
        $objCancel = $this->paymentService->getCancelSubScription(Auth::user()->email);

        if ($objCancel) {

            if (isset($objCancel['code']) &&  $objCancel['code'] == 400 ) {

                    return response()->json(
                        [
                            $this->error => $objCancel['message']
                        ],
                        400
                    );
            }
            if (isset($objCancel['plan_name'])) {

                $this->_cancelSingleCompany(
                    $objCancel['subcription_id'],
                    Auth::user()->id, $objCancel['company'],
                    $objCancel['id']);
            }

            return response()->json([
                'data' => $objCancel['flag']
            ], 200);
        }
        return response()->json([$this->error => __('messages.un_processable_request')], 400);

    }

    /**
     * Woo-commerce common store.
     * @param string $modal
     * @param array $data
     * @return array
     * @throws EquioException
     */
    public function storeJsonWooData($modal = '', $data = []) {

        $this->woocommerce = new Client(
            Config::get('custom_config.WOOCOMMERCE_API_URL'),
            Config::get('custom_config.WOOCOMMERCE_API_KEY'),
            Config::get('custom_config.WOOCOMMERCE_API_SECRET'),
            ['wp_api' => true, 'version' => '', 'wp_api_prefix' => '/wp-json/wc/v1']
        );

        try {
            return $this->woocommerce->post($modal, $data);
        } catch (HttpClientException $e) {
            throw new EquioException($e->getMessage());
        } catch
        (\Exception $e) {

            throw new EquioException($e->getMessage());

        }
    }

    private function _cancelSingleCompany(
        $subcriptionId = 0,
        $userId = null,
        $description = '',
        $planId = 0,
        $action = ''
    ) {

        $action = Config::get('custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS')['Cancel_Basic_Subscription'];
        $now = Carbon::now('utc')->toDateTimeString();
        $this->shortpositionActivityLogService
            ->storeShortpositionActivityLog(
                [
                    'user_id' => $userId,
                    'action' => $action,
                    'log' => json_encode(
                        array(
                            'plan_id' => $planId,
                            'companies' => $description,
                            'subscription_id' => $subcriptionId,
                            'created_at' => date(
                                "m-d-Y h:i:s", strtotime($now)
                            )
                        )
                    ),
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );

         $this->_moduleSubscriptionTrackerService
            ->updateSubscriptionStatus(
                ['subscription_status' => Config::get(
                    "custom_config.MODULE_SUBCRIPTION_TRACKERS_STATUS.cancel"
                )],
                $subcriptionId
            );

        return true;


    }

    /**add card create stripe
     * @param Request $request
     * @return mixed
     */
    public  function  addCardCreateStripe(Request $request){

        $customerPaymentMethod = $this->paymentService
            ->createCustomerCardOrSourceProcess(
                $request->all(),
                Auth::user()->email
            );
        if ($customerPaymentMethod['code'] == 400) {
            return response()->json(
                [
                    'error' => $customerPaymentMethod['message']
                ],
                400
            );
        }

        return response()->json(
            [
            'data' => 'success'
            ],
            200);



    }

}