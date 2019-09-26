<?php

namespace App\Services;

use App\Repositories\TickerRepository;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use App\Services\ModuleSubscriptionTrackerService;
use App\Equio\Helper;
use Illuminate\Support\Facades\Config;
use App\Equio\Exceptions\EquioException;
use App\Equio\Stripe\Stripe;

class ShortPositionService {

    private $_tickerRepository;
    private $_paymentService;
    private $_moduleSubscriptionTrackerService;
    private $_stripe;
        
    /**
     * ShortPositionService constructor.
     * 
     * @param TickerRepository                 $tickerRepository 
     * @param PaymentService                   $paymentService 
     * @param ModuleSubscriptionTrackerService $moduleSubscriptionTrackerService 
     */
    public function __construct(
        TickerRepository $tickerRepository, 
        PaymentService $paymentService, 
        ModuleSubscriptionTrackerService $moduleSubscriptionTrackerService,
        Stripe $stripe        
    ) {
        $this->_tickerRepository = $tickerRepository;
        $this->_paymentService = $paymentService;
        $this->_moduleSubscriptionTrackerService = $moduleSubscriptionTrackerService;
        $this->_stripe = $stripe;
        \Stripe\Stripe::setApiKey(Config::get('custom_config.STRIPE_API_KEY'));
        \Stripe\Stripe::setApiVersion(
            Config::get('custom_config.STRIPE_API_VERSION')
        );
        
    }

    /**
     * handle custom error message
     * @param $exception
     * @return mixed
     */
    public function errorMessage($exception) {
        $error['message'] = !empty($exception) ? $exception : '';
        $error['code'] = '400';
        return $error;
    }

    /**
     * get all company list from ticker table
     * @return array
     */
    public function getCompanyList() {
        return $this->_tickerRepository->getAllCompanies();
    }

    /**
     * get all plan list from Stripe
     * @return array
     */
    public function getPlanList() {
        $planlist_arrs= [];
        $obj_results = $this->_paymentService->getStipePlanDetails();
        $default =false;
        if($obj_results['code'] == 400){
            return $obj_results;
        }
        if(!empty($obj_results)) {
            if(isset($obj_results['data'])){
                foreach ($obj_results['data'] as $rows){
                    if(isset($rows->metadata->default)){
                        if(($rows->metadata->default) == 1){
                            $default =true;
                        }
                    };
                    if($rows->id != Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY'))
                    {
                        $planlist_arrs [] = [
                            'id'=>$rows->id,'name'=>$rows->nickname,
                            'amount'=>$rows->amount,'company_count'=>$rows->metadata->company,
                            'default'=>$default
                        ];
                    }
                }
            }
        }
        return $planlist_arrs;
    }

    /**
     * get active plan info
     * @return array|mixed|\Stripe\Collection
     */
    public function getActivePlanInfo() {
        $retArrs = [];
        $result = $this->_paymentService->getUserActiveSubscriptionDetails(Auth::user()->email);
        if ($result['code'] == 400) {
            $retArrs['code'] = $result['code'];
            $retArrs['message'] = $result['message'];
            return $retArrs;
        } else {
            if (isset($result['result'])) {
                if (!empty($result['result'])) {
                    $retArrs['plan_name'] = !empty($result['result']->items['data'][0]->plan->nickname) ? $result['result']->items['data'][0]->plan->nickname : '';
                    $retArrs['next_payment_date'] = !empty(date(" m-d-y", $result['result']->current_period_end)) ? date(" m-d-y", $result['result']->current_period_end) : '';
                    $retArrs['amount'] = ($result['result']->items['data'][0]->plan->amount) / 100;
                    $retArrs['plan_id'] = !empty($result['result']->items['data'][0]->plan->id) ? $result['result']->items['data'][0]->plan->id : '';
                    $retArrs['subcription_id'] = !empty($result['result']->id) ? $result['result']->id : '';
                    $retArrs['product'] = !empty($result['result']->items['data'][0]->plan->product) ? $result['result']->items['data'][0]->plan->product : '';
                    $retArr['code'] = $result['code'];
                }
            }
        }
        return $retArrs;
    }

    /**
     * get subscription status
     * @return array
     */
    public function subscriptionStatus() {
        $payload['code'] = '200';
        $payload['message'] = '';
        try {
            $user = Auth::user();
            $paidSubscriptionStart = $user->paid_subscription_start;
            $paidSubscriptionEnd = $user->paid_subscription_end;
            $userId = $user->id;
            $objHelper = new Helper();
            $subscription = $objHelper->userSubscription(
                $paidSubscriptionStart,
                $paidSubscriptionEnd
            );
            $shortPositionSubscription = $this->_moduleSubscriptionTrackerService
                ->shortPositionSubscriptionStatus($userId);
            if (!empty($shortPositionSubscription) && count($shortPositionSubscription) > 0) {
                $shortPositionSubscription = $shortPositionSubscription['subscription_status'];
            } else {
                $shortPositionSubscription = Config::get ( 'custom_config.MODULE_SUBCRIPTION_TRACKERS_STATUS.no_record' );
            }
            $payload['message'] = 'success';
            $payload['equio_subscription_status'] = $subscription;
            $payload['short_position_subscription_status'] = $shortPositionSubscription;
            return $payload;
        } catch (\Exception $e) {
            $payload = $this->errorMessage($e->getMessage());
            return $payload;
        }
    }

    /**
     * get subscription availability
     * @return array
     */
    public function subscriptionAvailability($companyCode) {
        $payload['code'] = '200';
        $payload['message'] = '';
        $subscriptionAvailability = false;
        $subscriptionForCompany = false;
        try {

            $subscriptionAvailabilityArr = $this->_moduleSubscriptionTrackerService
                ->subscriptionAvailability($companyCode, Auth::user()->id);

            if (!empty($subscriptionAvailabilityArr['short_position_subscription'])) {
                $subscriptionAvailability = true;
            }
            if (!empty($subscriptionAvailabilityArr['subscription_for_company'])) {
                $subscriptionForCompany = true;
            }
            $payload['message'] = 'success';
            $payload['short_position_subscription_status'] = $subscriptionAvailability;
            $payload['subscription_for_company'] = $subscriptionForCompany;
            return $payload;
        } catch (\Exception $e) {
            $payload = $this->errorMessage($e->getMessage ());
            return $payload;
        }
    }

    /**
     * get all company list from ticker table
     * @return array
     */
    public function getCompaniesByIds($idArr = array()) {
        return $this->_tickerRepository->getCompaniesByIds($idArr);
    }

    /**
     * Get user company list 
     * @param String $email
     * @return Array $plans
     */
    public function getUserCompanyList($email = '') {
        try {
            $plans = [];
            $planId = null;
            $plans['downgrade'] = true;
            $subscription = $this->_stripe->getSubscriptionByEmail($email);
            $subscriptionId = $subscription['data'][0]['id'];
            if (isset($subscriptionId)) {
                $currentPlan = $this->_moduleSubscriptionTrackerService->getPlan($subscriptionId);
            }
            if (!empty($subscription) && isset($subscription['data'][0])) {
                $subscriptionData = $subscription['data'][0];
                foreach ($subscriptionData['items']['data'] as $item) {
                    if ($currentPlan == $item['plan']['id']) {
                        $plans['downgrade'] = false;                        
                    }
                    if ($item['plan']['id'] == Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY')){
                        $plans['single_plan_id'] = $item['plan']['id'];
                    } else {
                        $plans['basic_plan_id'] = $item['plan']['id'];
                        $plans['basic_plan_name'] = $item['plan']['nickname'];
                        if (!$plans['downgrade']) {
                            $currentPlan = $plans['basic_plan_id'];
                        }
                    }
                }
                if (!$plans['downgrade']) {
                    $plans['next_payment_date']
                        = date('m/d/Y', $subscriptionData['current_period_end']);
                    $plans['basic_plan']
                        = explode(",", $subscriptionData['metadata']['basic_plan']);
                    $plans['additional_plan']
                        = explode(",", $subscriptionData['metadata']['additional_plan']);
                    if (count($plans['additional_plan'])==1 && empty($plans['additional_plan'][0])) {
                        $plans['additional_plan'] = [];
                    }
                } else {
                    $plans['basic_plan_id'] = $currentPlan;
                    $currentPlanDetails = $this->_stripe->getPlanById($currentPlan);
                    $plans['basic_plan_name'] = $currentPlanDetails['nickname'];
                    $plans['next_payment_date'] = '';
                    $plans['basic_plan']
                        = explode(",", $subscriptionData['metadata']['prev_basic_plan']);
                    $plans['additional_plan']
                        = explode(",", $subscriptionData['metadata']['additional_plan']);
                    if (count($plans['additional_plan'])==1 && empty($plans['additional_plan'][0])) {
                        $plans['additional_plan'] = [];
                    }

                    //$this->_stripe->updateDowngradeMetaData($subscriptionId);
                    
                }

                return $plans;
            } else {
                throw new EquioException("Subscription not found");
            }
        } catch (\Exception $e) {
            throw new EquioException($e->getMessage(), $e->getCode());
        }
    }

}
