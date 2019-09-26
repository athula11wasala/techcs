<?php

namespace App\Http\Controllers;

use App\Services\ShortPositionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Traits\ShortPositionValidator;
use App\Equio\Helper;
use Illuminate\Support\Facades\Auth;
use App\Equio\ShortPosition\AbstractShortPositionFactory;
use App\Services\ModuleSubscriptionTrackerService;
use App\Services\UserShortTrackerService;
use App\Services\ShortpositionActivityLogService;
use App\Services\PaymentService;
use Carbon\Carbon;
use  App\Models\ModuleSubscriptionTracker;
use App\Equio\Exceptions\EquioException;

class ShortPositionController extends ApiController {

    use ShortPositionValidator;

    private $shortPositionService;
    private $_moduleSubscriptionTrackerService;
    private $_userShortTrackerService;
    private $_shortpositionActivityLogService;
    private $paymentService;
    private $error = 'error';
    private $message = 'message';

    public function __construct(
        ShortPositionService $shortPositionService,
        ModuleSubscriptionTrackerService $moduleSubscriptionTrackerService,
        UserShortTrackerService $userShortTrackerService,
        ShortpositionActivityLogService $shortpositionActivityLogService,
        PaymentService $paymentService
    ) {
        $this->shortPositionService = $shortPositionService;
        $this->_moduleSubscriptionTrackerService = $moduleSubscriptionTrackerService;
        $this->_userShortTrackerService = $userShortTrackerService;
        $this->_shortpositionActivityLogService = $shortpositionActivityLogService;
        $this->paymentService = $paymentService;
    }

    /**
     * get all company list from ticker table
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyList(Request $request) {
        \Log::info("==== get short position company list controller ", ['u' => json_encode($request)]);
        $list = $this->shortPositionService->getCompanyList();
        return $this->respond($list);
    }

    /**
     * get all Plan list from Stripe
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPlanList(Request $request) {
        \Log::info("==== get stripe plan list controller ", ['u' => json_encode($request)]);
        $plan_lists = $this->shortPositionService->getPlanList();
        if ($plan_lists) {
            if (isset($plan_lists['code']) && $plan_lists['code'] == 400) {
                return response()->json([$this->error => $plan_lists['message']], 400);
            }
            return response()->json(['data' => $plan_lists], 200);
        }
        return response()->json([$this->error => __('messages.un_processable_request')], 400);
    }

    /**
     * get active planlist details
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivePlanDetails(Request $request) {
        \Log::info("==== get stripe current plan controller ", ['u' => json_encode($request)]);
        $plan_lists = $this->shortPositionService->getActivePlanInfo();
        if (!empty($plan_lists)) {
            if (isset($plan_lists['code'])) {
                if (($plan_lists['code'] == 400)) {
                    return response()->json([$this->error => $plan_lists['message']], 400);
                }
            }
            return response()->json(['data' => $plan_lists], 200);
        }
        return response()->json([$this->error => __('messages.un_processable_request')], 400);
    }

    /**
     * get subscription status
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscriptionStatus(Request $request) {
        $payload = $this->shortPositionService->subscriptionStatus();
        if ($payload) {
            if (isset($payload['code'])) {
                if (($payload['code'] == 400)) {

                    return response()->json([$this->error => $payload['message']], 400);
                }
            }
            return response()->json(['data' => $payload], 200);
        }
        return response()->json([$this->error => __('messages.un_processable_request')], 400);
    }

    /**
     * get subscription availability per company
     * @param Request $request
     * @return mixed
     */
    public function subscriptionAvailability(Request $request) {
        $validator = $this->companyCodeValidate($request->all());
        if ($validator->fails()) {
            $validateMessge = Helper::customErrorMsg($validator->messages());
            return $this->respondBadRequest(['error' => $validateMessge]);
        }
        $payload = $this->shortPositionService->subscriptionAvailability($request->companyCode);
        if ($payload) {
            if (isset($payload['code']) && ($payload['code'] == 400)) {
                $this->respondBadRequest(['error' => $payload['message']]);
            }
            return $this->respond(['data' => $payload]);
        }
        return $this->respondBadRequest(['error' => __('messages.un_processable_request')]);
    }

    /**
     * change  user subcription status using webhook
     * @param Request $request
     */
    public function subscriptionUserChangeStatus(Request $request) {
        $reqest_data = $request->all();
        if (isset($reqest_data['type']) && $reqest_data['type'] == "customer.subscription.deleted" &&
            $reqest_data['data']['object']['status'] == "canceled") {

            $id = !empty($reqest_data['data']['object']['id']) ?
                $reqest_data['data']['object']['id'] : '';
            $objModuleSubcriptionTracker = ModuleSubscriptionTracker::where("subscription_id", $id)->first();
            if (!empty($id) && !empty( $objModuleSubcriptionTracker) ) {
                $objModuleSubcriptionTracker->subscription_status = Config::get("custom_config.MODULE_SUBCRIPTION_TRACKERS_STATUS.cancel");
                $objModuleSubcriptionTracker->save();
                return response()->json(['data'=>true], 200);
            }
        }
        return $this->respondBadRequest(['error' => __('messages.un_processable_request')]);
    }

    /**
     * Get user company list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserCompanyList() {
        try {
            $planList = $this->shortPositionService->getUserCompanyList(Auth::user()->email);
            if (!empty($planList)) {
                return $this->respond($planList);
            } else {
                return $this->respondBadRequest(['error' => 'No plans']);
            }
        } catch (EquioException $e) {
            \Log::info("==== getUserCompanyList Error ", [$e->getMessage()]);
            return $this->respondBadRequest(
                ['error' => __('Subscription not found')]
            );
        } catch (\Exception $e) {
            \Log::info("==== getUserCompanyList Error ", [$e->getMessage()]);
            return $this->respondBadRequest(
                ['error' => __('messages.un_processable_request')]
            );
        }
    }

    /**
     * Cancel Plan
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelPlan(Request $request) {
        try {
            $companySymbols = [];
            $validator = $this->cancelPlanValidate($request->all());
            if ($validator->fails()) {
                return $this->respondBadRequest(['error' => $validator->errors()->first()]);
            }
            $companies = $request->input('company');
            if (is_array($companies)) {
                $companySymbols = $companies;
            } else {
                $companySymbols = array($companies);
            }

            $planId = $request->input('plan_id');
            $plan = AbstractShortPositionFactory::getFactory($planId)->getPlan();
            $result = $plan->cancelPlan($companySymbols);

            if ($planId == Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY') && $result['canceled']) {
                $this->_shortpositionActivityLogService->storeShortpositionActivityLog(
                    $result['short_position_activity_log_data']
                );
                return $this->respond(
                    ['canceled'=>$result['canceled'], 
                    'basic'=>$result['basic']]
                );
            } else if ($result['canceled']) {
                $this->_shortpositionActivityLogService
                    ->storeShortpositionActivityLog(
                        $result['short_position_activity_log_data']
                    );
                $this->_moduleSubscriptionTrackerService->updateSubscriptionStatus(
                    ['subscription_status' => Config::get(
                        "custom_config.MODULE_SUBCRIPTION_TRACKERS_STATUS.pending_cancel"
                    )],
                    $result['subscription_id']
                );
                return $this->respond(
                    [
                        'canceled'=>$result['canceled'],
                        'cancel_at'=>$result['cancel_at'],
                        'basic'=>$result['basic']
                    ]
                );
            } else {
                return $this->respondBadRequest(
                    ['error' => 'Could not cancel the plan']
                );
            }

        } catch (\Exception $e) {
            \Log::info("==== cancelPlan Error ", [$e->getMessage()]);
            return $this->respondBadRequest(
                ['error' => __('messages.un_processable_request')]
            );
        }

    }

    /**
     * Cancel Plan Webhook which fires at end of the billing period on cancel event
     *
     * @param Request $request
     *
     * @return void
     */
    public function cancelPlanWebHook(Request $request) {
        try {
            $subscription = $request->all();

            if (isset($subscription['data']['object']['metadata']['basic_plan'])) {
                $basicPlan = $subscription['data']
                ['object']['metadata']['basic_plan'];
                $metaData = $subscription['data']['object']['metadata'];
                $basicPlan = !empty($basicPlan) ? explode(',', $basicPlan) : [];
                $additionalPlan = array_key_exists('additional_plan', $metaData) ?
                    explode(
                        ',', $subscription['data']
                    ['object']['metadata']['additional_plan']
                    ) : [];
                $subscriptionId = $subscription['data']['object']['id'];
                $status = $subscription['data']['object']['status'];
                if ($status == 'canceled') {
                    $userId = $this->_moduleSubscriptionTrackerService
                        ->updateSubscriptionStatus(
                            ['subscription_status' => Config::get(
                                "custom_config.MODULE_SUBCRIPTION_TRACKERS_STATUS.cancel"
                            )],
                            $subscriptionId
                        );

                    $this->_userShortTrackerService->deleteUserShortTracker($userId);

                    $now = Carbon::now('utc')->toDateTimeString();
                    $this->_shortpositionActivityLogService
                        ->storeShortpositionActivityLog(
                            [
                                'user_id' => $userId,
                                'action' => Config::get(
                                    'custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS.Cancel_Basic_Subscription'
                                ),
                                'log' => json_encode(
                                    array(
                                        'companies' => $basicPlan,
                                        'basic_plan' => $basicPlan,
                                        'additional_plan' => $additionalPlan,
                                        'subscription_id' => $subscriptionId,
                                        'created_at' => date(
                                            "m-d-Y h:i:s", strtotime($now)
                                        ),
                                        'price' => '0.00'
                                    )
                                ),
                                'created_at' => $now,
                                'updated_at' => $now
                            ]
                        );
                }
            } else {
                \Log::info("==== cancelPlan Webhook Error ", ["No Basic Plan"]);
            }
        } catch (\Exception $e) {
            \Log::info("==== cancelPlan Webhook Error ", [$e->getMessage()]);
            return $this->respondBadRequest(
                ['error' => __('messages.un_processable_request')]
            );
        }

    }


    public function getNextPaymentDate() {
        $paymentDetail = '';
        try {
            /*  if (!empty(Auth::user()->stripe_id)) {

                  $paymentDetail = $this->paymentService->getSubcriptionPaymentDateById(Auth::user()->stripe_id);
              } else {

                  $stripeId = $this->paymentService->getCustomerId(Auth::user()->email);
                  $paymentDetail = $this->paymentService->getSubcriptionPaymentDateById($stripeId);
              }
            */
            $stripeId = $this->paymentService->getCustomerId(Auth::user()->email);
            $paymentDetail = $this->paymentService->getSubcriptionPaymentDateById($stripeId);
            if (!empty($paymentDetail) && isset($paymentDetail['next_payment_date'])) {
                return response()->json(['next_payment_date' => $paymentDetail['next_payment_date']], 200);
            } else {
                return response()->json([$this->error => 'messages.un_processable_request'], 400);
            }
        } catch (Exception $e) {
            return response()->json([$this->error => 'messages.un_processable_request'], 400);
        }

    }

    public function changeCompanies(Request $request) {
        try {
            $validator = $this->changeCompaniesValidate($request->all());
            if ($validator->fails()) {
                return $this->respondBadRequest(['error' => $validator->errors()->first()]);
            }
            $userId = Auth::user()->id;
            $pricePlanId = $request->input('price_plan_id');
            $companies = $request->input('company');
            $companySymbols = $this->shortPositionService->getCompaniesByIds($companies);
            $plan = AbstractShortPositionFactory::getFactory($pricePlanId)->getPlan();
            $result = $plan->changeCompanies($companySymbols);

            $this->_shortpositionActivityLogService->storeShortpositionActivityLog(
                $result['short_position_activity_log_data']);

            $isUpdated = $this->_userShortTrackerService->updateBasicCompanies($result, $userId);
            if ($isUpdated) {
                return $this->respond('updated successfully');
            } else {
                return $this->respondBadRequest(['error' => __('Could not change companies')]);
            }
        } catch (\Exception $e) {
            \Log::info("==== changeCompanies Error ", [$e->getMessage()]);
            return $this->respondBadRequest(
                ['error' => __('messages.un_processable_request')]
            );
        }
    }

}