<?php

namespace App\Equio\Stripe;

use App\Equio\Exceptions\EquioException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

/**
 * Class Stripe
 * @package App\Equio\Stripe
 */
class Stripe 
{


    /**
     * Get customer subscription
     *
     * @param String $email
     *
     * @return Array $subscription
     */
    public function getSubscriptionByEmail($email = '') {
        try {
            $customerId = $this->getCustomerId($email);
            $subscription['data'] = [];

            if (!empty($customerId)) {
                $basicPlans = Config::get('custom_config.ALL_BASIC_PLANS');
                
                foreach ($basicPlans as $basicPlan) {
                    if (count($subscription['data']) == 0) {
                        $subscription = \Stripe\Subscription::all(
                            ["customer" => $customerId, 'status' => 'active', 'plan' => $basicPlan]
                        );
                    }
                }
                
                return $subscription;
            }

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Get customer
     *
     * @param String $email
     *
     * @return Array $customer
     */
    public function getCustomerByEmail($email = '') {
        try {

            return \Stripe\Customer::all(
                array("email" => $email, 'limit' => 1)
            );

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Get existing card id
     *
     * @param String $email
     *
     * @return String $cardId
     */
    public function getCustomerCardId($email = '') {
        try {
            $customerId = $this->getCustomerId($email);
            $cardId = null;
            if (!empty($customerId)) {
                $cardList = \Stripe\Customer::retrieve($customerId)->sources->all(
                    array('limit' => 1)
                );

                if (isset($cardList['data'][0]['id'])) {
                    $cardId = $cardList['data'][0]['id'];
                }
            }

            return $cardId;
        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }
    }


    /**
     * Get customer Id
     *
     * @param String $email
     * @param String $token
     *
     * @return String $customerId
     */
    public function getCustomerId($email = '') {
        try {
            $customer = \Stripe\Customer::all(
                array("email" => $email, 'limit' => 1)
            );
            return $customer['data'][0]['id'];

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }
    }

    /**
     * Create customer
     *
     * @param String $email
     * @param String $token
     *
     * @return Object $customer
     */
    public function createCustomer($email = '', $token = '') {
        try {
            return \Stripe\Customer::create(
                array(
                    "email" => $email,
                    "source" => $token,
                )
            );
        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Create Subscription
     *
     * @param String $customerId
     * @param String $plan
     * @param Array $companies
     *
     * @return Array $subscription
     */
    public function createSubscription($customerId = '', $plan = '', $companies = []) {

        try {
            return \Stripe\Subscription::create(
                array(
                    "customer" => $customerId,
                    "items" => [
                        [
                            "plan" => $plan
                        ]
                    ],
                    "metadata" => array("basic_plan" => implode(',', $companies))
                )
            );

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Generate Module subscription tracker data array
     *
     * @param Array $data
     *
     * @return Array $moduleSubscriptionTrackerArr
     */
    public function generateModuleSubscriptionTrackerArray($data = []) {

        try {
            return $moduleSubscriptionTrackerArr = [
                'user_id' => $data['user_id'],
                'subscription_id' => $data['subscription_id'],
                'plan_id' => $data['plan_id'],
                'payment_gateway' => 'stripe',
                'subscription_status' => $data['subscription_status'],
                'subscription_name' => 'SHORT_POSITION',
            ];

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Generate user short tracker data array
     *
     * @param Array $data
     *
     * @return Array $userShortTrackerArray
     */
    public function generateUserShortTrackerArray($data = []) {

        try {

            $now = Carbon::now('utc')->toDateTimeString();
            $userShortTrackerArray = [];

            foreach ($data['company_symbols'] as $companySymbol) {
                $userShortTrackerData = [
                    'user_id' => $data['user_id'],
                    'symbol' => $companySymbol,
                    'created_at' => $now,
                    'updated_at' => $now
                ];

                array_push($userShortTrackerArray, $userShortTrackerData);

            }
            return $userShortTrackerArray;

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Generate short position activity log data array
     *
     * @param Array $data
     *
     * @return Array $shortPositionActivityLogArray
     */
    public function generateShortPositionActivityLogArray($data = []) {

        try {
            $now = Carbon::now('utc')->toDateTimeString();
            return [
                'user_id' => $data['user_id'],
                'action' => $data['action'],
                'log' => json_encode(
                    array(
                        'companies' => $data['company_symbols'],
                        'subscription_id' => $data['subscription_id'],
                        'created_at' => date("m-d-Y h:i:s", strtotime($now)),
                        'price' => $data['invoice_amount']
                    )
                ),
                'created_at' => $now,
                'updated_at' => $now
            ];

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Generate short position activity log data array
     *
     * @param Array $data
     *
     * @return Array $upcommingInvoice
     */
    public function getUpcomingInvoice($email = '') {

        try {
            $customerId = $this->getCustomerId($email);
            $invoice = \Stripe\Invoice::upcoming(["customer" => $customerId]);
            $upcommingInvoice['invoice_amount'] = $invoice->amount_due * 0.01;
            $upcommingInvoice['create_date'] = $invoice->date;
            $upcommingInvoice['code'] = '200';
            return $upcommingInvoice;

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }


    }

    /**
     * Update Subscription
     *
     * @param Array $data
     *
     * @return Array $subscription
     */
    public function updateSubscription($data = []) {

        try {
            return \Stripe\Subscription::update(
                $data['subscription_id'],
                array(
                    'items' => [
                        [
                            'plan' => $data['plan']
                        ]
                    ],
                    'metadata' => array(
                        'basic_plan' => $data['basic_plan'],
                        'additional_plan' => $data['additional_plan']
                    )
                )
            );

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Update Subscription meta data
     *
     * @param Array $data
     *
     * @return Array $subscription
     */
    public function updateSubscriptionMetaData($data = [], $canceled = '') 
    {

        try {
            return \Stripe\Subscription::update(
                $data['subscription_id'],
                array(
                    'metadata' => array(
                        'basic_plan' => $data['basic_plan'],
                        'additional_plan' => $data['additional_plan'],
                        'canceled' => $canceled
                    )
                )
            );

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Update Subscription item quantity
     *
     * @param Array $data
     *
     * @return Array $subscription
     */
    public function updateSubscriptionItemQuantity(
        $subscriptionId = '', $quantity = null, $prorate = true
    ) 
    {       

        try {
            $subscriptionItem = \Stripe\SubscriptionItem::retrieve($subscriptionId);
            $subscriptionItem->quantity = $quantity;
            $subscriptionItem->prorate = $prorate;
            $subscriptionItem->save();

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Retrieve subscription by subscription id
     *
     * @param String $subscriptionId
     *
     * @return Array $subscription
     */
    public function getSubscriptionById($subscriptionId = '') {

        try {
            return \Stripe\Subscription::retrieve(
                $subscriptionId
            );

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }

    /**
     * Retrieve proration amount
     *
     * @param String $email
     *
     * @return Array $data
     */
    public function getProrationAmount($email = '') {
        try {
            $quantity = 1;
            $subscriptionItem = '';
            $planId = 0;
            $items = [];
            $subscription['data'] = [];

            $customerId = $this->getCustomerId($email);
            $basicPlans = Config::get('custom_config.ALL_BASIC_PLANS');
                
            foreach ($basicPlans as $basicPlan) {
                if (count($subscription['data']) == 0) {
                    $subscription = \Stripe\Subscription::all(
                        ["customer" => $customerId, 'status' => 'active', 'plan' => $basicPlan]
                    );
                }
            }
            
            $subscriptionId = $subscription['data'][0]['id'];
            $subItems = $subscription['data'][0]['items']['data'];

            $plan = $this->getPlan(
                Config::get('custom_config.SHORT_POSITION_PLAN_META.single_company')
            );
            $planId = $plan['plan_id'];
            $data['interval'] = $plan['interval'];
            $data['amount'] = $plan['amount'];
            $data['nickname'] = $plan['nickname'];


            foreach ($subItems as $item) {

                if ($item['plan']['id'] == Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY')) {

                    $subscriptionItem = $item['id'];
                    $quantity = $quantity + $item['quantity'];
                }
            }

            if ($subscriptionItem == '') {

                $items = [
                    [
                        'plan' => $planId,
                        'quantity' => $quantity,
                    ],
                ];

            } else {
                $items = [
                    [
                        'id' => $subscriptionItem,
                        'quantity' => $quantity,
                    ],
                ];
            }


            $prorationDate = time();

            $invoice = \Stripe\Invoice::upcoming(
                [
                    'customer' => $customerId,
                    'subscription' => $subscriptionId,
                    'subscription_items' => $items,

                    'subscription_proration_date' => $prorationDate,
                    'subscription_prorate' => true
                ]
            );

            // Calculate the proration cost:
            $cost = 0;
            $currentProrations = [];
            foreach ($invoice->lines->data as $line) {

                if ($line->period->start == $prorationDate) {
                    array_push($currentProrations, $line);
                    $cost += $line->amount;
                }
            }

            $data['prorate_amount'] = $cost;
            $data['amount_remaining'] = $invoice['amount_remaining'];
            return $data;


        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }


    }

    /**
     * Retrieve plans by plan meta
     *
     * @param String $planMeta
     *
     * @return Array $data
     */
    public function getPlan($planMeta = '') {
        try {
            $plans = \Stripe\Plan::all();

            foreach ($plans['data'] as $plan) {
                if ($plan['metadata']['company'] == $planMeta) {
                    $data['plan_id'] = $plan['id'];
                    $data['interval'] = $plan['interval'];
                    $data['amount'] = $plan['amount'];
                    $data['nickname'] = $plan['nickname'];
                }
            }

            return $data;


        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }
    }

    /**
     * Add card to existing customer
     *
     * @param String $customerId
     * @param String $token
     *
     * @return void
     */
    public function addCardToExistingCustomer($customerId = '', $token = '') {
        try {
            $customer = \Stripe\Customer::retrieve($customerId);
            $cardId = $customer->sources->create(["source" => $token]);
            $this->changeDefaultCard($customerId, $cardId);
        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }
    }

    /**
     * Change the default card
     *
     * @param String $customerId
     * @param String $cardId
     *
     * @return void
     */
    public function changeDefaultCard($customerId = '', $cardId = '') {
        try {
            $customer = \Stripe\Customer::retrieve($customerId);
            $customer->default_source = $cardId;
            $customer->save();

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }
    }

    /**
     * Retrieve plans by plan Id
     *
     * @param String $planId
     *
     * @return Array $plan
     */
    public function getPlanById($planId = '') {
        try {
            return \Stripe\Plan::retrieve($planId);
        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }
    }

    /**
     * Update prev_basic_plan,prev_additional_plan meta data
     *
     * @return Array $subscription
     */
    public function updateDowngradeMetaData($subscriptionId = null) 
    {

        try {
            return \Stripe\Subscription::update(
                $subscriptionId,
                array(
                    'metadata' => array(
                        'prev_basic_plan' => '',
                        'prev_additional_plan' => '',
                    )
                )
            );

        } catch (Exception $exception) {
            throw new EquioException(
                $exception->getMessage(), $exception->getCode()
            );
        }

    }


}