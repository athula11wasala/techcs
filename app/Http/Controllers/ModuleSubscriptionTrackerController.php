<?php

namespace App\Http\Controllers;

use App\Equio\Helper;
use App\Services\ShortPositionService;
use App\Services\ModuleSubscriptionTrackerService;
use App\Services\UserShortTrackerService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use App\Traits\ModuleSubscriptionTrackerValidators;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use App\Services\ShortpositionActivityLogService;
use Illuminate\Support\Facades\Config;
use App\Equio\Stripe\Stripe;
use App\Equio\ShortPosition\AbstractShortPositionFactory;
use App\Models\User;
use Exception;


class ModuleSubscriptionTrackerController extends ApiController {

    use ModuleSubscriptionTrackerValidators;
    private $moduleSubscriptionTrackerService;
    private $userShortTrackerService;
    private $paymentService;
    private $shortpositionActivityLogService;
    private $_stripe;
    private $error = 'error';
    const MESSAGE = 'message';
    const CARD_ID = 'card_id';
    const SUBSCRIPTION_ID = 'subscriptionId';

    /**
     * Initializes a new instance of the ModuleSubscriptionTrackerController
     *
     * @param ModuleSubscriptionTrackerService $moduleSubscriptionTrackerService
     * @param PaymentService $paymentService
     * @param UserShortTrackerService $userShortTrackerService
     * @param ShortPositionService $shortPositionService
     * @param ShortpositionActivityLogService $shortpositionActivityLogService
     *
     * @return void
     */
    public function __construct(
        ModuleSubscriptionTrackerService $moduleSubscriptionTrackerService=null,
        PaymentService $paymentService=null,
        UserShortTrackerService $userShortTrackerService=null,
        ShortPositionService $shortPositionService=null,
        ShortpositionActivityLogService $shortpositionActivityLogService=null,
        Stripe $stripe
    ) {
        $this->moduleSubscriptionTrackerService = $moduleSubscriptionTrackerService;
        $this->paymentService = $paymentService;
        $this->userShortTrackerService = $userShortTrackerService;
        $this->shortPositionService = $shortPositionService;
        $this->shortpositionActivityLogService = $shortpositionActivityLogService;
        $this->_stripe = $stripe;
    }

    /**
     * subcription purchase plan
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchasePlan(Request $request) {

        try {
            $validator = $this->moduleSubscriptionValidate(
                $request->all(), $request->method()
            );

            if ($validator->fails()) {

                return response()->json(
                    [$this->error => $validator->errors()->first()], 400
                );
            }

            $data ['plan_id'] = $request->input('plan_id');
            $data ['company'] = $request->input('company');
            $data ['stripe_token'] = $request->input('stripe_token');
            $data ['card_id'] = $request->input('card_id');

            $result = $this->_purchase($data);

            if ($result['status'] == 'created') {
                $this->_storeSubscriptionTrackerDetails($result);
                return response()->json(
                    [SELF::MESSAGE => __('messages.subscription_create_success')],
                    200
                );
            } else {
                return response()->json(
                    [$this->error => 'Already user has a subscription'], 400
                );
            }
        } catch (Exception $exception) {
            return response()->json(
                [$this->error => 'Invalid customer or plan'], 400
            );
        }


    }

    private function _purchase($data = [])
    {
        if ($data ['plan_id'] == Config::get('custom_config.UNLIMITED_ALL_COMPANIES')) {
            $companySymbols = array("UNLIMITED");

        } else {
            $companies = $data ['company'];
            if (is_array($companies)) {
                $companySymbols = $this->shortPositionService
                    ->getCompaniesByIds($companies);
            } else {
                $companySymbols = array($companies);
            }

        }

        $planId = empty($data ['plan_id']) ?
            Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY')
            : $data ['plan_id'];

        $token = !empty($data ['stripe_token']) ? $data ['stripe_token'] : '';
        $cardId = !empty($data ['card_id']) ? $data ['card_id'] : '';
        $plan = AbstractShortPositionFactory::getFactory($planId)->getPlan();
        return $plan->purchasePlan($companySymbols, $token, $cardId, $planId);
    }

    /**
     * Store subscription tracker details
     * @param Array $subscriptionDataArr
     * @param Array $userShortTrackersDataArr
     * @param Array $shortpositionActivityLogsDataArr
     * @return
     **/
    private function _storeSubscriptionTrackerDetails($result = [])
    {
        try {
            \DB::beginTransaction();

            if (array_key_exists("module_subscription_tracker_data", $result)) {
                $this->moduleSubscriptionTrackerService
                    ->storeSubscriptionTrackerDetails(
                        $result['module_subscription_tracker_data']
                    );
            }

            if (array_key_exists("prev_subscription_id", $result)) {
                $this->moduleSubscriptionTrackerService
                    ->updateSubscriptionStatus(
                        ['subscription_status' =>Config::get(
                            "custom_config.MODULE_SUBCRIPTION_TRACKERS_STATUS.cancel"
                        )],
                        $result['prev_subscription_id']
                    );
                $this->userShortTrackerService
                    ->deleteUserShortTracker($result['user_id']);

                $this->shortpositionActivityLogService
                    ->storeShortpositionActivityLog(
                        $result['prev_short_position_activity_log_data']
                    );
            }
            $this->userShortTrackerService
                ->createUserShortTracker($result['user_short_tracker_data']);


            $this->shortpositionActivityLogService
                ->storeShortpositionActivityLog(
                    $result['short_position_activity_log_data']
                );

            \DB::commit();

        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json(
                [$this->error => __('messages.un_processable_request')], 400
            );
        }
    }

    /**
     * purchase Single Company
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseSingleCompany(Request $request)
    {
        try {
            $validator = $this->moduleSubscriptionValidate(
                $request->all(), $request->method()
            );

            if ($validator->fails()) {

                return response()->json(
                    [$this->error => $validator->errors()->first()], 400
                );
            }

            $data ['plan_id'] = $request->input('plan_id');
            $data ['company'] = $request->input('company');
            $data ['stripe_token'] = $request->input('stripe_token');

            $result = $this->_purchase($data);

            if ($result['status'] == 'updated') {
                $this->_storeSubscriptionTrackerDetails($result);
                return response()->json(
                    [SELF::MESSAGE => 'Updated the plan successfully'], 200
                );
            } else {
                return response()->json(
                    [$this->error => 'Could not update the plan successfully'], 400
                );
            }
        } catch (Exception $exception) {
            return response()->json(
                [$this->error => 'Could not update the plan successfully'], 400
            );
        }
    }

    /**
     * get Added Amount
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAddedAmount(Request $request) 
    {
        try {
            $upcomingInvoice = $this->paymentService
                ->getProrationAmount(Auth::user()->email);

            if (!empty($upcomingInvoice)) {
                $costDetails = ['prorate_amount' =>
                 $upcomingInvoice['prorate_amount'],
                    'amount_remaining' => $upcomingInvoice['amount_remaining'],
                    'interval' => $upcomingInvoice['interval'],
                    'amount' => $upcomingInvoice['amount'],
                    'plan' => $upcomingInvoice['nickname'],
                    'next_payment_date' => $upcomingInvoice['next_payment_date']
                ];

                return response()->json(['data' => $costDetails], 200);
            } else {
                return response()->json(
                    [$this->error => 'Could not get prorated amount successfully'
                    ],
                    400
                );
            }

        } catch (Exception $e) {
            
            return response()->json(
                [$this->error => 'Could not get prorated amount successfully'
                ],
                400
            );
        }
    }

    /**
     * update subcription purchase plan
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchasePlanCompanyUpdate(Request $request) {

        $subscriptionStatus = '0';
        $upGrade = true;
        $proRateAmount=0;
        $validator = $this->moduleSubscriptionValidate($request->all(), 'PUT');
        if ($validator->fails()) {
            return response()->json([$this->error => $validator->errors()->first()], 400);
        }

        $customerPaymentMethod = $this->paymentService->createCustomerCardOrSourceProcess($request->all(),

            Auth::user()->email);

        if ($customerPaymentMethod['code'] == 400) {
            return response()->json(['error' => $customerPaymentMethod['message']], 400);
        }
        $proRateAmount = $this->paymentService->getProrationAmountBase(Auth::user()->email,$request->input('price_plan'))['prorate_amount'];
        $subscription = $this->paymentService->getSubscription(Auth::user()->email);
        if (empty($subscription[SELF::SUBSCRIPTION_ID])) {
            return response()->json([$this->error => 'There is no any subscription'], 400);
        } else {
            // create new subscription
            if (!empty($subscription['customer'])) {
                if (!empty($request->input('currentcompany'))) {
                    $companies = $request->input('currentcompany');
                } else {
                    $companies = $request->input('company');
                }
                $companySymbols = $this->shortPositionService->getCompaniesByIds($companies);
                $currentPlanId = $this->paymentService->getSubcriptionPlanInfo($subscription['subscriptionId'])['plan_id'];
                if ($currentPlanId == Config::get('custom_config.BASIC_THREE_COMPANIES')) {
                    $upGrade = true;
                }
                if ($currentPlanId == Config::get('custom_config.PLUS_TEN_COMPANIES')) {
                    $requestPricePlan = $request->input('price_plan');
                    if ($requestPricePlan == Config::get('custom_config.BASIC_THREE_COMPANIES')) {
                        $upGrade = false;
                    }
                    if ($requestPricePlan == Config::get('custom_config.UNLIMITED_ALL_COMPANIES')) {
                        $upGrade = true;
                    }
                }
                if ($currentPlanId == Config::get('custom_config.UNLIMITED_ALL_COMPANIES')) {
                    $upGrade = false;
                }

                if ($request->input('price_plan') == $currentPlanId) {
                    $upGrade = false;
                    $dbPlan=  Helper::getModuleSucriptionPlanId(Auth::user()->id);

                }


                $updatedSubscription = $this->paymentService->updateSubscriptionMetaBasicPlan(
                    $subscription[SELF::SUBSCRIPTION_ID], $companySymbols, $request->input('price_plan'), $upGrade);
                if ($updatedSubscription['code'] == 400) {
                    return response()->json([$this->error => $updatedSubscription['message']], 400);
                } else {
                    if ($upGrade == true) {
                        $this->createShortTrackerArrWithLog($upGrade,
                            $request->input('price_plan'), $companySymbols, $subscription,[],$proRateAmount);
                    } else {
                        $companies = $request->input('currentcompany');
                        $modifyDowngradeSymbol = $this->shortPositionService->getCompaniesByIds($companies);
                        $companies = $request->input('company');
                        $companySymbol = $this->shortPositionService->getCompaniesByIds($companies);
                        if (count($modifyDowngradeSymbol) > 10) {
                            $modifyDowngradeSymbol = null;
                            $modifyDowngradeSymbol[] = strtoupper(Config::get('custom_config.SHORT_POSITION_PLAN_META')['unlimited']);
                        }
                        $this->createShortTrackerArrWithLog($upGrade,
                            $request->input('price_plan'), $companySymbol, $subscription, $modifyDowngradeSymbol,$proRateAmount);
                    }
                    return response()->json(['message' => __('messages.subscription_update_success')], 200);
                }
            } else {
                return response()->json([$this->error => 'Invalid customer or plan'], 400);
            }
        }
    }


    /**
     * stroe subcription track & log
     * @param $userShortTrackersDataArr
     * @param $shortpositionActivityLogsDataArr
     * @param $userId
     * @return bool|\Illuminate\Http\JsonResponse
     */
    private function storeSubscriptionUpdateTrackerDetails($userShortTrackersDataArr, 
        $shortpositionActivityLogsDataArr,
        $userId
    ) {
        try {
            \DB::beginTransaction();

            $isUserTrackerCreated = $this->userShortTrackerService
                ->createExistUserShortTracker(
                    $userShortTrackersDataArr,
                    $userId
                );

            if ($isUserTrackerCreated) {
                $this->shortpositionActivityLogService->storeShortpositionActivityLog($shortpositionActivityLogsDataArr);

            }

            \DB::commit();

        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json([$this->error => __('messages.un_processable_request')], 400);
        }
        return true;
    }


    /**
     * update subcription using webhook
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateExistCustomerSymbol(Request $request)
    {
        $reqest_data = $request->all();
        $email = '';
       
        if (isset($reqest_data['type']) && ($reqest_data['type'] == "charge.captured") && isset($reqest_data['data']['object']['customer'])) {
            $customerId =  $reqest_data['data']['object']['customer'];
            $userId = "";
            $objMetaDetails = $this->paymentService->getCustSubcriptionMetaDetailsById($customerId);
            if ($objMetaDetails['code'] == 400) {
                return response()->json([$this->error => $objMetaDetails['message']], 400);
            }
            $objUser = '';// Helper::getUserByStripeId($customerId);
            if (!empty($objUser)) {
                $userId = $objUser->id;
                $email = $objUser->email;
            } else {
                $custStripeDetails = $this->paymentService->getCustomerDetails($customerId);
                if (isset($custStripeDetails['customer']->email)) {
                    $objUser = Helper::getUserByEmail($custStripeDetails['customer']->email);
                    $userId = $objUser->id;
                    $email = $custStripeDetails['customer']->email;
                }
            }
            if (isset($userId)) {
                $userShortTrackersDataArr = array();
                $userShortTrackersDataArr = array();
                $now = Carbon::now('utc')->toDateTimeString();
                //Cancel Process
                if (count($objMetaDetails['canceled']) > 0) {
                    $this->_cancelSingleCompany(
                        $objMetaDetails,
                        $userId,
                        $objMetaDetails['canceled'],
                        Config::get(
                            'custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS.Canceled_Single_Company'
                        )
                    );
                }
                foreach ($objMetaDetails['symbol'] as $companySymbol) {
                    $userShortTrackersData = [
                        'user_id' => $userId,
                        'symbol' => $companySymbol,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                    array_push($userShortTrackersDataArr, $userShortTrackersData);
                }
                $upcomingInvoice = $this->paymentService->getUpcomingInvoiceInfo($objUser->email);
                if (!empty($upcomingInvoice) && $upcomingInvoice['code'] == 200) {
                  //  $invoiceAmount = $upcomingInvoice['invoice_amount'];
                    $createDate = $upcomingInvoice['create_date'];
                } else {
                    $invoiceAmount = 0;
                    $createDate = '';
                }
                $objPaymentService = new PaymentService();
                $obj_active_subcription_info = $objPaymentService->getUserActiveSubscriptionId($email);
                if(isset($obj_active_subcription_info['plan_id'])){

                    $stripePlanInfo = $objPaymentService->getPlanById($obj_active_subcription_info['plan_id']);
                    $invoiceAmount = $stripePlanInfo['amount'];
                }

                $shortpositionActivityLogsData = [
                    'user_id' => $userId,
                    'action' => (Config::get('custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS')['Downgrade']),
                    'log' => json_encode(array(
                        'companies' => $objMetaDetails['symbol'],
                        'subscription_id' =>
                            $objMetaDetails['id'],
                        'created_at' => date("m-d-Y h:i:s", $createDate),
                        'price' => $invoiceAmount,
                        'subcription_start_date' => $objMetaDetails['start_date'],
                        'subcription_end_date' => $objMetaDetails['end_date'],
                    )),
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                $this->storeSubscriptionUpdateTrackerDetails($userShortTrackersDataArr,
                    $shortpositionActivityLogsData, $userId);
                $shortpositionActivityLogsData['action'] = (Config::get('custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS')['Auto_RenewalOfPlan']);
                $this->storeSubscriptionActivityLogDetails($shortpositionActivityLogsData);
                $this->moduleSubscriptionTrackerService->updateSubscriptionPlanId(
                    ['plan_id' => $obj_active_subcription_info['plan_id']],
                    $objMetaDetails['id']);
                $this->_stripe->updateDowngradeMetaData($objMetaDetails['id']);
                $obj_active_subcription_info = $objPaymentService->getUserActiveSubscriptionId($email);

                return response()->json(['message' => __('messages.company_symbol_update_success')],
                    200);
            }
        }
        return response()->json([$this->error => __('messages.un_processable_request')], 400);
    }


    /**
     * stroe subscription log
     * @param $shortpositionActivityLogsDataArr
     * @return bool|\Illuminate\Http\JsonResponse
     */
    private function storeSubscriptionActivityLogDetails($shortpositionActivityLogsDataArr

    ) {
        try {
            \DB::beginTransaction();

            $this->shortpositionActivityLogService->storeShortpositionActivityLog($shortpositionActivityLogsDataArr);

            \DB::commit();

        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json([$this->error => __('messages.un_processable_request')], 400);
        }
        return true;
    }




    /**
     * create short tracker with log details
     * @param $upGrade
     * @param string $pricePlan
     * @param string $companySymbols
     * @param string $subscription
     * @param string $modifyDowngradeSymbol
     */
    public function createShortTrackerArrWithLog(
        $upGrade,
        $pricePlan = '',
        $companySymbols = '',
        $subscription = '',
        $modifyDowngradeSymbol = '',
        $proRateAmount = ''
    ) {
        $userShortTrackersDataArr = array();
        $now = Carbon::now('utc')->toDateTimeString();
        $action = '';
        if ($upGrade == true) {
            $action = Config::get('custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS')['Upgrade'];
            if (!empty($pricePlan) && $pricePlan == Config::get('custom_config.UNLIMITED_ALL_COMPANIES')) {
                $companySymbols[] = strtoupper(Config::get('custom_config.SHORT_POSITION_PLAN_META')['unlimited']);
                $userShortTrackersData = [
                    'user_id' => Auth::user()->id,
                    'symbol' => strtoupper(Config::get('custom_config.SHORT_POSITION_PLAN_META')['unlimited']),
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                array_push($userShortTrackersDataArr, $userShortTrackersData);
            } else {
                foreach ($companySymbols as $companySymbol) {
                    $userShortTrackersData = [
                        'user_id' => Auth::user()->id,
                        'symbol' => $companySymbol,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                    array_push($userShortTrackersDataArr, $userShortTrackersData);
                }
            }
            $this->moduleSubscriptionTrackerService->updateSubscriptionPlanId(['plan_id' => $pricePlan], $subscription['subscriptionId']);
        } else {
            $action = Config::get('custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS')['Downgrade_trigger'];
            if (!empty($modifyDowngradeSymbol)) {
                // $companySymbols = $modifyDowngradeSymbol;
                if (count($companySymbols) > 10) {
                    $companySymbols = null;
                    $companySymbols[] = strtoupper(Config::get('custom_config.SHORT_POSITION_PLAN_META')['unlimited']);
                }
                foreach ($companySymbols as $symbol) {
                    $userShortTrackersData = [
                        'user_id' => Auth::user()->id,
                        'symbol' => $symbol,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    array_push($userShortTrackersDataArr, $userShortTrackersData);
                }
            }
            $companySymbols = $modifyDowngradeSymbol;
        }

        $upcomingInvoice = $this->paymentService->getUpcomingInvoiceInfo(Auth::user()->email);
        if (!empty($upcomingInvoice) && $upcomingInvoice['code'] == 200) {
          //  $invoiceAmount = $upcomingInvoice['invoice_amount'];
            $createDate = $upcomingInvoice['create_date'];
        }

        if($upGrade == false){

            $invoiceAmount = "0.00";

        }else {

            $proRateObj = $proRateAmount;
            if (!empty($proRateObj)) {

                $invoiceAmount =   $proRateAmount ?? '0.00';
            }

        }

        if (!empty($pricePlan) && $pricePlan == Config::get('custom_config.UNLIMITED_ALL_COMPANIES')) {
            $companySymbols = null;
            $companySymbols[] = strtoupper(Config::get('custom_config.SHORT_POSITION_PLAN_META')['unlimited']);
        }
        $shortpositionActivityLogsData = [
            'user_id' => Auth::user()->id,
            'action' => $action,
            'log' => json_encode(array(
                'companies' => $companySymbols,
                'subscription_id' =>
                    $subscription['subscriptionId'],
                'subcription_start_date' => $upcomingInvoice['next_paymet_start'],
                'subcription_end_date' => $upcomingInvoice['next_paymet_end'],
                'created_at' => date("m-d-Y h:i:s", $createDate),
                'price' => $invoiceAmount
            )),
            'created_at' => $now,
            'updated_at' => $now
        ];
        $this->storeSubscriptionUpdateTrackerDetails($userShortTrackersDataArr,
            $shortpositionActivityLogsData, Auth::user()->id);
    }


    /**
     * cancel Single Company
     * @param array $metaDetails
     * @param null $userId
     * @param array $canceledCompanies
     * @param string $action
     */
    private function _cancelSingleCompany(
        $metaDetails = [],
        $userId = null,
        $canceledCompanies = [],
        $action = ''
    ) {

        $canceledCompanies = implode(',', $canceledCompanies);

        $plan = $this->_stripe->getPlan(
            Config::get(
                'custom_config.SHORT_POSITION_PLAN_META.single_company'
            )
        );

        $now = Carbon::now('utc')->toDateTimeString();
        $this->shortpositionActivityLogService
            ->storeShortpositionActivityLog(
                [
                    'user_id' => $userId,
                    'action' => $action,
                    'log' => json_encode(
                        array(
                            'companies' => explode(',', $canceledCompanies),
                            'subscription_id' =>  $metaDetails['id'],
                            'created_at' => date(
                                "m-d-Y h:i:s", strtotime($now)
                            ),
                            'price' => $plan['amount']
                        )
                    ),
                    'created_at'=> $now,
                    'updated_at'=> $now
                ]
            );
    }



}