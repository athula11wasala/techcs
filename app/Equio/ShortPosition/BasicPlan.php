<?php
namespace App\Equio\ShortPosition;

use App\Equio\ShortPosition\Contracts\PlanInterface;
use App\Equio\Stripe\Stripe;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use App\Equio\Exceptions\EquioException;

class BasicPlan extends Stripe implements PlanInterface
{

    /**
     * BasicPlan constructor.
     */
    public function __construct() 
    {
        
        \Stripe\Stripe::setApiKey(Config::get('custom_config.STRIPE_API_KEY'));
        \Stripe\Stripe::setApiVersion(
            Config::get('custom_config.STRIPE_API_VERSION')
        );
        
    }

    /**
     * Cancel basic plan - cancel the plan in stripe at the end of billing period
     * 
     * @param String $companies 
     * 
     * @return Array $data
     */
    public function cancelPlan($companies = []) 
    {  
        try {
            $subscription = $this->getSubscriptionByEmail(Auth::user()->email); 
            $userId = Auth::user()->id;
            
            $subscriptionDetails = $this->getSubscriptionById(
                $subscription['data'][0]['id']
            );
            $subscriptionDetails->cancel_at_period_end = true;
            $canceledSubscription = $subscriptionDetails->save();
                
            if (!empty($canceledSubscription->cancel_at)) {
                $data['cancel_at'] = date(
                    'm/d/Y', $canceledSubscription->cancel_at
                );
                $data['canceled'] = true;

                $data['short_position_activity_log_data']
                    = $this->generateShortPositionActivityLogArray(
                        [
                            'user_id' => $userId,
                            'company_symbols' => $companies,
                            'subscription_id' => $subscription['data'][0]['id'],
                            'invoice_amount' => '0.00',
                            'action' => Config::get(
                                'custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS.Cancel_Basic_Plan_Pending'
                            )
                        ]
                    );
                $data['subscription_id'] = $subscription['data'][0]['id'];
            } else {
                $data['canceled'] = false;
            } 
            $data['basic'] = true;      

            return $data;
        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    public function purchasePlan(
        $companies = [], $token = '', $cardId = '', $planId = ''
    ) 
    {  
        try {
            
            $email = Auth::user()->email;
            $userId = Auth::user()->id;
            $data = [];
            $customer = $this->getCustomerByEmail($email);

            if (!empty($customer['data'][0]->id)) {
                $customerId = $customer['data'][0]->id;
                $subscription = $this->getSubscriptionByEmail($email);
                if (!empty($subscription['data'][0]) && !($subscription['data'][0]['cancel_at_period_end'])) {
                    $data['status'] = 'exists';
                    return  $data;
                } else {
                    if (isset($subscription['data'][0]['cancel_at_period_end']) 
                        && $subscription['data'][0]['cancel_at_period_end']
                    ) {
                        $subscriptionDetails = $this->getSubscriptionById(
                            $subscription['data'][0]['id']
                        );
                        $subscriptionDetails->cancel();
                        $basicPlans = $subscriptionDetails['metadata']['basic_plan'];
                                    
                        $data['prev_subscription_id'] = $subscription['data'][0]['id'];
                        $data['prev_short_position_activity_log_data'] 
                            = $this->generateShortPositionActivityLogArray(
                                [
                                    'user_id' => $userId,
                                    'company_symbols' => explode(',', $basicPlans),
                                    'subscription_id' => $subscription['data'][0]['id'],
                                    'invoice_amount' => '0.00',
                                    'action' => Config::get(
                                        'custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS.Cancel_Basic_Subscription'
                                    )
                                ]
                            );
                        
                    }
                    if (!empty($token)) {
                        $this->addCardToExistingCustomer($customerId, $token);
                    } else if (!empty($cardId)) {
                        $this->changeDefaultCard($customerId, $cardId);
                    }
                } 
            } else {
                $customer = $this->createCustomer($email, $token); 
                $customerId = $customer->id;
            }
            
            $subscription = $this->createSubscription(
                $customerId, $planId, $companies
            );

            if ($subscription->status == 'active') {
                $subscriptionStatus = Config::get(
                    "custom_config.MODULE_SUBCRIPTION_TRACKERS_STATUS.active"
                );
            }
            
            $data['status'] = 'created';

            $data['user_id'] = $userId;

            $data['module_subscription_tracker_data'] 
                = $this->generateModuleSubscriptionTrackerArray(
                    [
                        'user_id' => $userId,
                        'subscription_id' => $subscription->id,
                        'plan_id' => $planId,
                        'subscription_status' => $subscriptionStatus,
                    ]
                ); 
                

            $data['user_short_tracker_data'] 
                = $this->generateUserShortTrackerArray(
                    [
                        'user_id' => $userId,
                        'company_symbols' => $companies
                    ]
                );
                

            $invoice =  $this->getUpcomingInvoice($email);
            $data['short_position_activity_log_data'] 
                = $this->generateShortPositionActivityLogArray(
                    [
                        'user_id' => $userId,
                        'company_symbols' => $companies,
                        'subscription_id' => $subscription->id,
                        'invoice_amount' => $invoice['invoice_amount'],
                        'action' => Config::get(
                            'custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS.Purchase'
                        )
                    ]
                );   

            return $data;
            
        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Change companies
     * 
     * @param String $companies 
     * 
     * @return Array $data
     */
    public function changeCompanies($companySymbols = []) 
    {  
        try {
            $subscription = $this->getSubscriptionByEmail(Auth::user()->email); 
            $userId = Auth::user()->id;
            
            $subscriptionDetails = $this->getSubscriptionById(
                $subscription['data'][0]['id']
            );
            
            $basicPlans = explode(
                ',', $subscriptionDetails["metadata"]["basic_plan"]
            );

            if (isset($subscriptionDetails["metadata"]["additional_plan"])) {
                $metaDataAdditionalPlan = $subscriptionDetails["metadata"]["additional_plan"];
            } else {
                $metaDataAdditionalPlan = '';
            }

            if (count($basicPlans) < count($companySymbols)) {
                $data['status'] = 'downgrade';
            } else {
                $this->updateSubscriptionMetaData(
                    [
                        'subscription_id' => $subscription['data'][0]['id'],
                        'basic_plan' => implode(',', $companySymbols),
                        'additional_plan' => $metaDataAdditionalPlan,
                    ]
                );
                $data['status'] = 'update';
            }

            $data['short_position_activity_log_data']
                = $this->generateShortPositionActivityLogArray(
                    [
                        'user_id' => $userId,
                        'company_symbols' => array('added' => $companySymbols, 'removed' => $basicPlans),
                        'subscription_id' => 'N/A',
                        'invoice_amount' => '0.00',
                        'action' => Config::get(
                            'custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS.Change_Companies'
                        )
                    ]
                );

            $data['user_short_tracker_data'] 
                = $this->generateUserShortTrackerArray(
                    [
                        'user_id' => $userId,
                        'company_symbols' => $companySymbols
                    ]
                );
            
            $data['additional_plan'] = explode(',', $metaDataAdditionalPlan);
            return $data;
        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

}