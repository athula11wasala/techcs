<?php

namespace App\Equio\ShortPosition;

use App\Equio\ShortPosition\Contracts\PlanInterface;
use Illuminate\Support\Facades\Auth;
use App\Equio\Stripe\Stripe;
use App\Equio\Exceptions\EquioException;
use Illuminate\Support\Facades\Config;

class SinglePlan extends Stripe implements PlanInterface {

    public function cancelPlan($companies = []) {
        try {
            
            $email = Auth::user()->email;
            $userId = Auth::user()->id;
            $data = [];
            $quantity = 0;
            $data['canceled'] = false;
            $subscription = $this->getSubscriptionByEmail($email);
            $subscriptionDetails = $this->getSubscriptionById(
                $subscription['data'][0]['id']
            );

            $items = $subscriptionDetails['items']['data'];

            foreach ($items as $item) {

                if ($item['plan']['id'] == Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY')) {
                    $quantity += $item['quantity'];
                    $subId = $item['id'];
                }

            }
            
            $metaDataBasicPlan = $subscriptionDetails["metadata"]["basic_plan"];
            $metaData = $subscriptionDetails["metadata"];
            
            if (isset($metaData['canceled'])) {
                $metaDataCanceledPlan = $subscriptionDetails["metadata"]["canceled"].",".implode(',', $companies);
            } else {
                $metaDataCanceledPlan = implode(',', $companies);
            }

            if ($quantity == 1 || count($companies)==$quantity) {
                $this->updateSubscriptionMetaData(
                    [
                        'subscription_id' => $subscription['data'][0]['id'],
                        'basic_plan' => $metaDataBasicPlan,
                        'additional_plan' => '',
                    ],
                    $metaDataCanceledPlan
                ); 
                $subcriptionItem = \Stripe\SubscriptionItem::retrieve($subId);
                $subcriptionItem->delete();
                $data['canceled'] = true;


            } else if ($quantity > 1 && count($companies)<$quantity) {
                
                $metaDataAdditionalPlan = explode(
                    ',', $subscriptionDetails["metadata"]["additional_plan"]
                );
               
                if (count(array_intersect($companies, $metaDataAdditionalPlan)) > 0) {
                    $difference = array_diff($metaDataAdditionalPlan, $companies);
                    $metaDataAdditionalPlan = implode(
                        ',', $difference
                    ); 

                    $this->updateSubscriptionMetaData(
                        [
                            'subscription_id' => $subscription['data'][0]['id'],
                            'basic_plan' => $metaDataBasicPlan,
                            'additional_plan' => $metaDataAdditionalPlan,
                        ],
                        $metaDataCanceledPlan
                    );  
                    $this->updateSubscriptionItemQuantity(
                        $subId, count($difference), false
                    );  
                    $data['canceled'] = true;            
                }
            }            

            $plan = $this->getPlan(
                Config::get(
                    'custom_config.SHORT_POSITION_PLAN_META.single_company'
                )
            );

            $data['short_position_activity_log_data']
                = $this->generateShortPositionActivityLogArray(
                    [
                        'user_id' => $userId,
                        'company_symbols' => $companies,
                        'subscription_id' => $subscription['data'][0]['id'],
                        'invoice_amount' =>'0.00', //$plan['amount'],
                        'action' => Config::get(
                            'custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS.Cancel_Single_Company_Pending'
                        )
                    ]
                );
            
            $data['basic'] = false;  
            return $data;

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }
    }

    public function purchasePlan(
        $companies = [], $token = '', $cardId = '', $planId = ''
    ) {
        try {
            $email = Auth::user()->email;
            $userId = Auth::user()->id;
            $data = [];
            $quantity = 1;
            $subscription = $this->getSubscriptionByEmail($email);
            $subscriptionDetails = $this->getSubscriptionById(
                $subscription['data'][0]['id']
            );
            $invoice = $this->getProrationAmount($email);

            $items = $subscriptionDetails['items']['data'];

            foreach ($items as $item) {

                if ($item['plan']['id'] == $planId) {
                    $quantity += $item['quantity'];
                    $subId = $item['id'];
                }

            }

            $metaDataBasicPlan = $subscriptionDetails["metadata"]["basic_plan"];

            if ($quantity == 1) {
                $this->updateSubscription(
                    [
                        'subscription_id' => $subscription['data'][0]['id'],
                        'plan' => $planId,
                        'basic_plan' => $metaDataBasicPlan,
                        'additional_plan' => implode("", $companies),
                    ]
                );


            } else if ($quantity > 1) {
                $metaDataAdditionalPlan
                    = $subscriptionDetails["metadata"]["additional_plan"] . ',' .
                    implode("", $companies);
                $this->updateSubscriptionMetaData(
                    [
                        'subscription_id' => $subscription['data'][0]['id'],
                        'basic_plan' => $metaDataBasicPlan,
                        'additional_plan' => $metaDataAdditionalPlan,
                    ]
                );
                $this->updateSubscriptionItemQuantity($subId, $quantity);


            }

            $data['status'] = 'updated';

            $data['user_short_tracker_data']
                = $this->generateUserShortTrackerArray(
                    [
                        'user_id' => $userId,
                        'company_symbols' => $companies
                    ]
                );

            
            $data['short_position_activity_log_data']
                = $this->generateShortPositionActivityLogArray(
                    [
                        'user_id' => $userId,
                        'company_symbols' => $companies,
                        'subscription_id' => $subscription['data'][0]['id'],
                        'invoice_amount' => $invoice['prorate_amount'] * 0.01,
                        'action' => Config::get(
                            'custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS.Single_Purchase'
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
        // change companies code here

    }

}