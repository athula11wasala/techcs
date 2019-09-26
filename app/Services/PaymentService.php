<?php

namespace App\Services;

use App\Equio\Helper;
use App\Repositories\ReportRepository;
use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Stripe\Error;
use App\Models\User;
use Laravel\Cashier\Cashier;
use App\Models\ModuleSubscriptionTracker;


class PaymentService {

    private $response;
    private $reportRepository;

    /**
     * PaymentService constructor.
     * @param ReportRepository|null $reportRepository
     */
    public function __construct(ReportRepository $reportRepository = null)
    {
        $this->reportRepository = $reportRepository;
        \Stripe\Stripe::setApiKey(Config::get('custom_config.STRIPE_API_KEY'));
        \Stripe\Stripe::setApiVersion(Config::get('custom_config.STRIPE_API_VERSION'));
    }

    /**
     * handle custom error message
     * @param $exception
     * @return mixed
     */
    public function errorMessage($exception)
    {
        $body = $exception->getJsonBody();
        $err = $body['error'];
        $retArr['message'] = !empty($err['message']) ? $err['message'] : '';
        $retArr['code'] = '400';
        return $retArr;
    }

    public function errorDefaultMessage($exception)
    {
        $retArr['message'] = !empty($exception) ? $exception : '';
        $retArr['code'] = '400';
        return $retArr;
    }

    /**
     * create customer with soure or card
     * @param $request
     * @param $email
     * @return bool|mixed|null|string
     */
    public function createCustomerCardOrSourceProcess($request, $email)
    {

        $existCardId = (!empty($request['card_id']))
            ? ($request['card_id']) : '';
        $token = (!empty($request['token']))
            ? ($request['token']) : '';
        $object = (!empty($request['object'])) ? ($request['object']) : '';
        $customerObject = $this->checkExistCustomer(
            $email,
            $existCardId,
            $token,
            $object
        );
        if (isset($customerObject['code']) && $customerObject['code'] == 400) {

            return $customerObject;
        }

        if ($customerObject == false) {
            try {
                $customer = \Stripe\Customer::create(
                    array(
                    "email" => $email,
                    "source" => $token,
                    )
                );

            } catch (\Stripe\Error\Card $exception) {

                $retArr = $this->errorMessage($exception);
                return $retArr;

            } catch (\Stripe\Error\RateLimit $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\InvalidRequest $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\Authentication $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\ApiConnection $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\Base $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (Exception $exception) {

                return $this->errorDefaultMessage($exception->getMessage());

            }

        }

    }

    /**
     *  get customer info
     * @param $id
     * @return Array
     */
    public function getCustomerDetails($id)
    {
        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['customer'] = '';

        try {
            $customer = \Stripe\Customer::retrieve($id);
            $retArr['customer'] = $customer;
            return $retArr;
        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }

    /**
     * get customer id info
     * @param $email
     * @return mixed|null|string
     */
    public function getCustomerId($email) {
        $customerId = null;
        try {
            $customer = \Stripe\Customer::all(
                array("email" => $email,
                    'limit' => 1)
            );
            if (isset($customer['data'][0]->id)) {
                $customerId = !empty($customer['data'][0]->id)
                    ? $customer['data'][0]->id : '';

            }
            return $customerId;

        } catch (\Stripe\Error\Card $exception) {

            $retArr = $this->errorMessage($exception);
            return $retArr;

        } catch (\Stripe\Error\RateLimit $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }

    /**
     * get customer card info
     * @param $email
     * @return array
     */
    public function getCustomerCardDetails($email) {

        $customerId = $this->getCustomerId($email);
        $sourceCardList = null;
        $cardList = [];
        if (!empty($customerId)) {
            // $sourceId = \Stripe\Customer::retrieve ( $customerId )->default_source;
            // $sourceCardList = \Stripe\Source::retrieve ( $sourceId )->card;
            $objCardList = \Stripe\Customer::retrieve($customerId)->sources->all(array(
                'limit' => 10, 'object' => 'card'));

            $sourceCardList = \Stripe\Customer::retrieve($customerId)->sources->all(array(
                'limit' => 10, 'object' => 'source'));

            foreach ($objCardList['data'] as $rows) {
                $cardList[] =
                    ['object' => 'card',
                        'card_id' => $rows['id'],
                        'brand' => $rows['brand'],
                        'last4' => $rows['last4'],
                        'exp_month' => $rows['exp_month'],
                        'exp_year' => $rows['exp_year']];

            }

        }
        if (!empty($sourceCardList)) {
            foreach ($sourceCardList['data'] as $rows) {
                $cardList[] =
                    ['object' => 'source',
                        'card_id' => $rows['id'],
                        'brand' => $rows->card['brand'],
                        'last4' => $rows->card['last4'],
                        'exp_month' => $rows->card['exp_month'],
                        'exp_year' => $rows->card['exp_year']];

            }

        }

        return $cardList;

    }

    /**
     * get customer source info
     * @param $email
     * @return array
     */
    public function getCustomerSourcetails($email) {

        $customerId = $this->getCustomerId($email);
        $sourceCardList = null;
        $cardList = [];
        if (!empty($customerId)) {

            $sourceCardList = \Stripe\Customer::retrieve($customerId)->sources->all(array(
                'limit' => 10, 'object' => 'source'));

        }
        if (!empty($sourceCardList)) {
            foreach ($sourceCardList['data'] as $rows) {
                $cardList[] =
                    ['object' => 'source',
                        'card_id' => $rows['id'],
                        'brand' => $rows->card['brand'],
                        'last4' => $rows->card['last4'],
                        'exp_month' => $rows->card['exp_month'],
                        'exp_year' => $rows->card['exp_year']];

            }
        }

        return $cardList;
    }


    /**
     * assing card to exist customer
     * @param $customerId
     * @param $token
     * @return bool|mixed
     */
    public function addCardExistCustomers($customerId, $token) {

        try {

            $customer = \Stripe\Customer::retrieve($customerId);
            $card_id = $customer->sources->create(array("source" => $token));
            $this->setDefaultCard($customerId, $card_id);

        } catch (\Stripe\Error\Card $exception) {

            $retArr = $this->errorMessage($exception);
            return $retArr;

        } catch (\Stripe\Error\RateLimit $exception) {

            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\InvalidRequest $exception) {

            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }
        return true;

    }

    /**
     * stripe payment process
     * @param $request
     * @return bool|mixed|null|string
     */
    public function primaryPaymentProcess($request) {

        $productNames = '';
        $email = Auth::user()->email;
        $paymentAmount = (!empty($request['payment_amount'])) ? ($request['payment_amount']) : '';
        $existCardId = (!empty($request['card_id'])) ? ($request['card_id']) : '';
        $token = (!empty($request['token'])) ? ($request['token']) : '';
        $object = (!empty($request['object'])) ? ($request['object']) : '';
        $customerId = $this->checkExistCustomer($email, $existCardId, $token, $object);
        $coupon_discount = isset($request['discount']) ? $request['discount'] : 0;

        if (isset($customerId['code']) && $customerId['code'] == 400) {

            return $customerId;
        }

        if (isset($request['product'])) {
            if ((array_column($request['product'], 'id'))) {
                // extract product ids to array from request
                $product_ids = array_column($request['product'], 'id');
                // get product name
                $productNamesArray = $this->reportRepository->getReportNames($product_ids);
                $productNamesFiltered = array_column($productNamesArray, 'name');
                $productNames = implode(' ', $productNamesFiltered);
            }

        }

        if (isset($request['subcription_name'])) {
            $productNames = $request['subcription_name'];
        }

        if (isset($customerId['code']) && (isset($customerId['message']))) {

            return $customerId;
        }
        if ($customerId == false) {

            try {
                $customer = \Stripe\Customer::create(array(
                    "email" => $email,
                    "source" => $token,
                ));


            } catch (\Stripe\Error\Card $exception) {

                $retArr = $this->errorMessage($exception);
                return $retArr;

            } catch (\Stripe\Error\RateLimit $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\InvalidRequest $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\Authentication $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\ApiConnection $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\Base $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (Exception $exception) {
                return $this->errorDefaultMessage($exception->getMessage());
            }

            $customerId = $customer['id'];
            $objSelCustomer = $this->getCustomerDetails($customer['id']);
            if ($objSelCustomer['code'] == 400) {
                return $objSelCustomer;
            }
            $this->userUpdateStripeDetails($email, $objSelCustomer['customer']);

        }
        return $this->addPaymentCharge($customerId, ($paymentAmount - $coupon_discount), $productNames);
    }


    /**
     * add payment charge
     * @param null $customerId
     * @param int $amount
     * @param string $productNames
     * @return bool|mixed
     */
    public function addPaymentCharge($customerId = null, $amount = 0, $productNames = '') {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['status'] = 'success';
        try {
            $charge = \Stripe\Charge::create(array(
                "amount" => ($amount * 100),
                "currency" => "USD",
                "customer" => $customerId,
                "description" => !empty($productNames) ? $productNames : ''
            ));

            if (!empty($charge['status'])) {
                $retArr['status'] = $charge['status'];
                $retArr['transaction_id'] = !empty($charge['balance_transaction']) ? $charge['balance_transaction'] : '';
                $retArr['stripe_order_id'] = !empty($charge['id']) ? $charge['id'] : '';
                return $retArr;
            }
            return false;

        } catch (\Stripe\Error\Card $exception) {

            $retArr = $this->errorMessage($exception);
            return $retArr;

        } catch (\Stripe\Error\RateLimit $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }


    /**
     * check customer
     * @param $email
     * @param null $existCardId
     * @param null $token
     * @param null $object
     * @return bool|mixed|null|string
     */
    public function checkExistCustomer($email, $existCardId = null, $token = null, $object = null) {
        $customerId = null;
        try {
            $customer = \Stripe\Customer::all(array("email" => $email, 'limit' => 1));
            if (isset($customer['data'][0]->id)) {
                ;
                $customerId = !empty($customer['data'][0]->id) ? $customer['data'][0]->id : '';
                if (!empty($existCardId) && !empty($object)) {
                    if ($object == "card" || $object == "source") {
                        $catchErrorToken = $this->setDefaultCard($customerId, $existCardId);
                        if (isset($catchErrorToken['code']) && (isset($catchErrorToken['message']))) {
                            return $catchErrorToken;
                        }

                    }
                }
                if (!empty($token)) {
                    $catchErrorToken = $this->addCardExistCustomers($customerId, $token);
                    if (isset($catchErrorToken['code']) && (isset($catchErrorToken['message']))) {
                        return $catchErrorToken;
                    }

                }
            }
            return $customerId;

        } catch (\Stripe\Error\Card $exception) {

            $retArr = $this->errorMessage($exception);
            return $retArr;

        } catch (\Stripe\Error\RateLimit $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }

    /**
     * update user entity
     * @param $email
     * @param $objCustomer
     */
    public function userUpdateStripeDetails($email, $objCustomer) {
        $updateUsers = DB::table("users")->where("email", $email)->update(["stripe_id" => $objCustomer['id']]);
    }

    /**
     * cancel stripe subceription
     * @param $email
     * @return bool|mixed
     */
    public function getCancelSubScription($email) {
        $retArr['code'] = '200';
        $retArr['message'] = '';
        $customer_id = $this->getCustomerId($email);
        $retArr['flag'] = false;
        $retArr['company'] = [];

        if (empty($customer_id)) {

            $retArr['flag'] = true;
            return $retArr;
        }
        try {

            $objUser = User::where("email", $email)->select("id")->first();
            $subcription_id = $this->getStripeSubcriptionId($objUser->id);
            $objUserSubcription_list = \Stripe\Subscription::retrieve($subcription_id);

            if (isset($objUserSubcription_list->id)) {
                $retArr['subcription_id'] = $objUserSubcription_list->id;

                if (isset($objUserSubcription_list->metadata['basic_plan'])) {
                    $retArr['company'] = explode(",", $objUserSubcription_list->metadata['basic_plan']);

                }
                foreach ($objUserSubcription_list->items['data'] as $rows) {

                    if ($rows->plan['id'] != Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY')) {
                        $retArr['id'] = $rows->plan->id;
                        $retArr['plan_name'] = $rows->plan->nickname;

                    }
                }
            }


        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

        if (empty($objUserSubcription_list)) {

            $retArr['flag'] = true;
            return $retArr;
        } else {

            try {

                $sub = \Stripe\Subscription::retrieve(($objUserSubcription_list->id));
                $sub->cancel();
            } catch (\Stripe\Error\ApiConnection $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\Base $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (Exception $exception) {
                return $this->errorDefaultMessage($exception->getMessage());
            }


        }
        $retArr['flag'] = true;
        return $retArr;
    }

    /**
     * get customer's default card Info
     * @param $email
     * @return mixed
     */
    public function getCustomerDefaultCardDetails($email) {
        $customerId = $this->getCustomerId($email);
        $data['customer'] = !empty($customerId) ? $customerId : null;
        $data['card_id'] = null;
        if (!empty($customerId)) {
            $objCardList = \Stripe\Customer::retrieve($customerId)->sources->all(array(
                'limit' => 1));

            if (isset($objCardList['data'][0]['id'])) {

                $data['card_id'] = $objCardList['data'][0]['id'];
            }
        }

        return $data;
    }

    /**
     * get stripe Plan Info
     * @return mixed|\Stripe\Collection
     */
    public function getStipePlanDetails() {
        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['plan'] = '';
        $product_id = Config::get('custom_config.STRIPE_PRODUCT_KEY');

        try {
            $retArr = \Stripe\Plan::all(["product" => $product_id]);
            return $retArr;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }

    /**
     * check and get active subscriptions by email
     * @param string $email
     * @return mixed
     */
    public function getUserActiveSubscriptionId($email = '') {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['status'] = true;
        $retArr['plan_id'] = '';
        $customer_id = $this->getCustomerId($email);

        if (empty($customer_id)) {

            $retArr['code'] = '400';
            $retArr['message'] = 'Invalid Email';
            return $retArr;
        }

        try {
            $objUser = User::where("email", $email)->select("id")->first();
            $subcription_id = $this->getStripeSubcriptionId($objUser->id);
            $objUserSubscription_list = \Stripe\Subscription::retrieve($subcription_id);

            foreach ($objUserSubscription_list->items['data'] as $rows) {

                if ($rows->plan['id'] != Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY')) {

                    $retArr['plan_id'] = $rows->plan['id'];
                }

            }
            $retArr['customer'] = $customer_id;
            $retArr['subscriptionId'] = !empty($objUserSubscription_list->id) ?
                $objUserSubscription_list->id : '';

            return $retArr;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }

    /**
     * check and get existing subscriptions by email
     * @param string $email
     * @param string $token
     * @return mixed
     */
    public function getSubscription($email = '', $token = '') {
        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['status'] = true;
        $customer_id = $this->getCustomerId($email);

        if (empty($customer_id)) {
            // create customer
            $customer = $this->createCustomer($email, $token);
            if ($customer->id) {
                $customer_id = $customer->id;
            } else {
                $retArr['code'] = '400';
                $retArr['message'] = 'customer creation error';
                $retArr['status'] = false;
                return $retArr;
            }
        }

        try {
            $objUser = User::where("email", $email)->select("id")->first();
            $subcription_id = $this->getStripeSubcriptionId($objUser->id);
            $objUserSubscription_list = \Stripe\Subscription::retrieve($subcription_id);
            $retArr['customer'] = $customer_id;
            $retArr['subscriptionId'] = !empty($objUserSubscription_list->id) ?
                $objUserSubscription_list->id : '';

            return $retArr;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }

    /**
     * create a subscription plan on stripe
     * @param string $customer
     * @param string $plan
     * @return mixed|\Stripe\Subscription
     */
    public function createSubscription($customer = '', $plan = '', $companyCodes = array()) {
        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['status'] = true;

        try {

            $retArr = \Stripe\Subscription::create(array(
                "customer" => $customer,
                "items" => [
                    [
                        "plan" => $plan
                    ]
                ],
                "metadata" => array("basic_plan" => implode(',', $companyCodes))
            ));
            return $retArr;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }


    /**
     * create a customer on stripe
     * @param null $email
     * @param null $token
     * @return mixed|\Stripe\Customer
     */
    public function createCustomer($email = null, $token = null) {
        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['status'] = true;
        try {
            $retArr = \Stripe\Customer::create(array(
                "email" => $email,
                "source" => $token,
            ));
            return $retArr;
        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }

    /**
     * check and get active subscriptions by email
     * @param string $email
     * @return mixed
     */
    public function getUserActiveSubscriptionDetails($email = '') {
        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['status'] = true;
        $customer_id = $this->getCustomerId($email);

        if (empty($customer_id)) {

            $retArr['code'] = '400';
            $retArr['message'] = 'Invalid Email';
            return $retArr;
        }

        try {
            $objUser = User::where("email", $email)->select("id")->first();
            $subcription_id = $this->getStripeSubcriptionId($objUser->id);
            $retArr['result'] = \Stripe\Subscription::retrieve($subcription_id);
            return $retArr;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }

    /**
     * add a new plan or update item quantity of a plan in an existing subscription on stripe
     * @param string $subscription
     * @param array $companies
     * @return mixed|\Stripe\Subscription
     */
    public function updateSubscription($subscription = '', $company = '') {
        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['status'] = true;
        $planId = 0;
        $quantity = 1;
        $subId = 0;

        try {

            $plans = \Stripe\Plan::all();

            foreach ($plans['data'] as $plan) {
                if ($plan['metadata']['company'] ==
                    Config::get('custom_config.SHORT_POSITION_PLAN_META.single_company')) {
                    $planId = $plan['id'];
                }
            }

            $subscriptionDetails = \Stripe\Subscription::retrieve($subscription);

            $items = $subscriptionDetails['items']['data'];

            foreach ($items as $item) {

                if ($item['plan']['id'] == $planId) {
                    $quantity += $item['quantity'];
                    $subId = $item['id'];
                }

            }

            $metaDataBasicPlan = $subscriptionDetails["metadata"]["basic_plan"];

            if ($quantity == 1) {
                $retArr = \Stripe\Subscription::update($subscription, array(
                    "items" => [
                        [
                            'plan' => $planId
                        ]
                    ],
                    "metadata" => array("basic_plan" => $metaDataBasicPlan, 'additional_plan' => $company)
                ));

            } else if ($quantity > 1) {
                $metaDataAdditionalPlan = $subscriptionDetails["metadata"]["additional_plan"] . ',' . $company;
                $retArr = \Stripe\Subscription::update($subscription, array(
                    "metadata" => array("basic_plan" => $metaDataBasicPlan,
                        'additional_plan' => $metaDataAdditionalPlan)
                ));
                $subscriptionItem = \Stripe\SubscriptionItem::retrieve($subId);
                $subscriptionItem->quantity = $quantity;
                $subscriptionItem->save();

            }

            return $retArr;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }


    /**
     * add charge description of transaction
     * @param $trsnaction_id
     * @param $description
     */
    public function setChargeDescription($trsnaction_id, $description = '', $payment_type = '') {

        try {

            $objTransaction = \Stripe\Charge::retrieve(
                $trsnaction_id
            );

            if ($payment_type == "report_payment") {

                $objTransaction->description = $description . " " . $objTransaction->description;

            } else {

                $objTransaction->description = $description;
            }
            $objTransaction->save();
            return true;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }

    public function getUpcomingInvoiceInfo($email) {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['customer'] = '';
        $retArr['invoice_amount'] = '';
        $retArr['create_date'] = '';

        try {

            $customerId = $this->getCustomerId($email);
            $objInvoice = \Stripe\Invoice::upcoming(["customer" => $customerId]);
            $retArr['next_paymet_start'] = date("m-d-Y h:i:s", $objInvoice->lines['data'][0]->period->start);
            $retArr['next_paymet_end'] = date("m-d-Y h:i:s", $objInvoice->lines['data'][0]->period->end);
            $retArr['invoice_amount'] = $objInvoice->amount_due / 100;
            $retArr['create_date'] = $objInvoice->date;
            return $retArr;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }

    /**
     * stripe customr soruce createProcess
     * @param $request
     * @return bool|mixed|null|string
     */
    public function customrSoruceCreateProcess($request) {

        $productNames = '';
        $email = Auth::user()->email;
        $paymentAmount = (!empty($request['payment_amount'])) ? ($request['payment_amount']) : '';
        $existCardId = (!empty($request['card_id'])) ? ($request['card_id']) : '';
        $token = (!empty($request['token'])) ? ($request['token']) : '';
        $object = (!empty($request['object'])) ? ($request['object']) : '';
        $customerInfo = $this->checkExistCustomerWithSource($email, $existCardId, $token, $object);

        if (isset($request['subcription_name'])) {
            $productNames = $request['subcription_name'];
        }

        if (isset($customerInfo['code']) && (isset($customerInfo['message']))) {

            return $customerInfo;
        }
        if ($customerInfo == false) {

            try {
                $customer = \Stripe\Customer::create(array(
                    "email" => $email,

                ));

                $sourceObj = \Stripe\Source::create([
                    "type" => "card",
                    "currency" => "usd",
                    "token" => $token,
                    "owner" => [
                        "email" => $customer->email
                    ]
                ]);

                $sourceId = $sourceObj->id;
                $customer->sources->create(["source" => $sourceId]);
                $this->setDefaultCard($customer->id, $sourceId);


            } catch (\Stripe\Error\Card $exception) {

                $retArr = $this->errorMessage($exception);
                return $retArr;

            } catch (\Stripe\Error\RateLimit $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\InvalidRequest $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\Authentication $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\ApiConnection $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (\Stripe\Error\Base $exception) {
                $retArr = $this->errorMessage($exception);
                return $retArr;
            } catch (Exception $exception) {
                return $this->errorDefaultMessage($exception->getMessage());
            }

            $customerId = $customer->id;
            $sourceId = $sourceId;
            $customerInfo = ['customer_id' => $customerId, 'source_id' => $sourceId];

            $objSelCustomer = $this->getCustomerDetails($customer['id']);
            if ($objSelCustomer['code'] == 400) {
                return $objSelCustomer;
            }
            $this->userUpdateStripeDetails($email, $objSelCustomer['customer']);

        }
        return $this->addPaymentCharge($customerInfo['customer_id'], ($paymentAmount), $productNames);

    }


    /**
     * check customer
     * @param $email
     * @param null $existCardId
     * @param null $token
     * @param null $object
     * @return bool|mixed|null|string
     */
    public function checkExistCustomerWithSource($email, $existCardId = null, $token = null, $object = null) {
        $customerId = null;
        try {
            $customer = \Stripe\Customer::all(array("email" => $email, 'limit' => 1));
            if (isset($customer['data'][0]->id)) {
                $customerId = !empty($customer['data'][0]->id) ? $customer['data'][0]->id : '';
                if (!empty($existCardId) && !empty($object)) {
                    if ($object == "source") {

                        $defaultSourceObj = $this->setDefaultCard($customerId, $existCardId);
                        if (isset($defaultSourceObj['code']) && (isset($defaultSourceObj['message']))) {
                            return $defaultSourceObj;
                        }
                        $sourceId = !empty($defaultSourceObj) ? $defaultSourceObj : '';


                    }

                }
                if (!empty($token)) {
                    $sourceExistCustomers = $this->addSourceExistCustomers($customerId, $token);
                    if (isset($sourceExistCustomers['code']) && (isset($sourceExistCustomers['message']))) {
                        return $sourceExistCustomers;
                    }
                    $sourceId = !empty($sourceExistCustomers) ? $sourceExistCustomers : '';

                }
            }

            if (isset($customerId) && isset($sourceId)) {

                return ['customer_id' => $customerId, 'source_id' => $sourceId];
            } else {
                return false;
            }

        } catch (\Stripe\Error\Card $exception) {

            $retArr = $this->errorMessage($exception);
            return $retArr;

        } catch (\Stripe\Error\RateLimit $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());

        }

    }

    /**
     * assing card to exist customer
     * @param $customerId
     * @param $token
     * @return bool|mixed
     */
    public function addSourceExistCustomers($customerId, $token) {
        try {

            $customer = \Stripe\Customer::retrieve($customerId);
            $sourceObj = \Stripe\Source::create([
                "type" => "card",
                "currency" => "usd",
                "token" => $token,
                "owner" => [
                    "email" => $customer->email
                ]
            ]);

            $sourceId = $sourceObj->id;
            $customer->sources->create(["source" => $sourceId]);
            $this->setDefaultCard($customerId, $sourceId);
            return $sourceId;

        } catch (\Stripe\Error\Card $exception) {

            $retArr = $this->errorMessage($exception);
            return $retArr;

        } catch (\Stripe\Error\RateLimit $exception) {

            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\InvalidRequest $exception) {

            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }


    public function getProrationAmount($email) {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['prorate_amount'] = '';
        $retArr['amount_remaining'] = '';
        $retArr['interval'] = '';
        $retArr['amount'] = 0;
        $retArr['nickname'] = '';
        $quantity = 1;
        $subscriptionItem = '';
        $planId = 0;
        $items = [];

        try {
            $customer_id = $this->getCustomerId($email);
            //   $subscription = \Stripe\Subscription::all(["customer" => $customer_id, 'status' => 'active']);
            $objUser = User::where("email", $email)->select("id")->first();
            $subcription_id = $this->getStripeSubcriptionId($objUser->id);
            $subscription = \Stripe\Subscription::retrieve($subcription_id);
            $subscriptionId = $subscription['id'];
            $subItems = $subscription['items']['data'];

            $plan = $this->getPlan(Config::get('custom_config.SHORT_POSITION_PLAN_META.single_company'));
            $planId = $plan['plan_id'];
            $retArr['interval'] = $plan['interval'];
            $retArr['amount'] = $plan['amount'];
            $retArr['nickname'] = $plan['nickname'];


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


            $proration_date = time();

            $invoice = \Stripe\Invoice::upcoming([
                'customer' => $customer_id,
                'subscription' => $subscriptionId,
                'subscription_items' => $items,

                'subscription_proration_date' => $proration_date,
                'subscription_prorate' => true

            ]);

            // Calculate the proration cost:
            $cost = 0;
            $current_prorations = [];
            foreach ($invoice->lines->data as $line) {

                if ($line->period->start == $proration_date) {
                    array_push($current_prorations, $line);
                    $cost += $line->amount;
                }
            }
            $nextPaymentDate = date("m/d/Y h:i:s", $invoice['next_payment_attempt']);
            $nextPaymentDate=substr($nextPaymentDate, 0, 10);
            $retArr['next_payment_date'] = $nextPaymentDate;
            $retArr['prorate_amount'] = $cost;
            $retArr['amount_remaining'] = $invoice['amount_remaining'];


        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);

        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);

        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);

        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);

        } catch (Exception $exception) {
            return $exception;
        }

        return $retArr;
    }

    public function getPlan($planMeta) {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['plan_id'] = 0;
        $retArr['interval'] = '';
        $retArr['amount'] = 0;
        $retArr['nickname'] = '';

        try {
            $plans = \Stripe\Plan::all();

            foreach ($plans['data'] as $plan) {
                if ($plan['metadata']['company'] == $planMeta) {
                    $retArr['plan_id'] = $plan['id'];
                    $retArr['interval'] = $plan['interval'];
                    $retArr['amount'] = $plan['amount'];
                    $retArr['nickname'] = $plan['nickname'];
                }
            }
            return $retArr;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (Exception $exception) {
            return $exception;
        }

    }

    public function getPlanByPlanId($planId) {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['plan_meta'] = '';

        try {
            $plans = \Stripe\Plan::all();

            foreach ($plans['data'] as $plan) {
                if ($plan['id'] == $planId) {
                    $retArr['plan_meta'] = $plan['metadata']['company'];
                }
            }

            return $retArr;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (Exception $exception) {
            return $exception;
        }

    }

    /**
     * @param string $subscriptionId
     * @param array $companyCodes
     * @param string $price_plan
     * @param bool $upGrade
     * @return mixed
     */
    public function updateSubscriptionMetaBasicPlan($subscriptionId = '', $companyCodes = array(), $price_plan = '', $upGrade) {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['status'] = true;
        $retArr['up_grade'] = false;
        $flag_additional_comapny = false;

        try {

            $objSubcripton = \Stripe\Subscription::retrieve($subscriptionId);
            $metadata_result = $objSubcripton->metadata;
            $append_meta_arr['basic_plan'] = implode(',', $companyCodes);
            if (isset($metadata_result['additional_plan'])) {

                $append_meta_arr['additional_plan'] = $metadata_result['additional_plan'];
                $append_meta_arr['prev_additional_plan'] = $metadata_result['additional_plan'];
            }

            if($upGrade == true){

                $append_meta_arr['prev_basic_plan'] = $metadata_result['basic_plan'];
            }elseif(!isset($metadata_result['status']) &&  $upGrade == false) {
                $append_meta_arr['prev_basic_plan'] = $metadata_result['basic_plan'];
            }
            elseif(isset($metadata_result['status']) &&  $upGrade == false) {

                $status_arr = (explode(",", $metadata_result['status']));
                if(end($status_arr) == 1){
                    $append_meta_arr['prev_basic_plan'] = $metadata_result['basic_plan'];
                }

            }

            $objSubcripton->prorate = false;
            if (!empty($price_plan) && $price_plan == Config::get('custom_config.UNLIMITED_ALL_COMPANIES')) {
                $items = [];
                $retArr['up_grade'] = true;
                $append_meta_arr = [];
                $append_meta_arr['basic_plan'] = strtoupper(Config::get('custom_config.SHORT_POSITION_PLAN_META')['unlimited']);


                foreach ($objSubcripton->items['data'] as $rows) {

                    if ($rows->plan['id'] == Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY')) {
                        $flag_additional_comapny = true;

                        $subcription_item = \Stripe\SubscriptionItem::retrieve($rows->id);
                        $subcription_item->delete();

                    }

                    if ($rows->plan['id'] != Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY')) {
                        $si = \Stripe\SubscriptionItem::retrieve($rows->id);
                        $si->plan = $price_plan;
                        $si->save();
                    }
                }

            } else {

                $company_new_cnt = count($companyCodes);
                $comapny_current_cnt = count(explode(",", $objSubcripton->metadata['basic_plan']));
                if ($company_new_cnt > $comapny_current_cnt) {

                    $retArr['up_grade'] = true;
                }

                foreach ($objSubcripton->items['data'] as $rows) {

                    if ($rows->plan['id'] != Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY')) {
                        $si = \Stripe\SubscriptionItem::retrieve($rows->id);
                        $si->plan = $price_plan;
                        $si->save();

                    }
                }
            }

            if ($upGrade == true) {

                $objSubcripton->prorate = true;

            } else {

                if (isset($metadata_result['status'])) {

                    $append_meta_arr['status'] = $metadata_result['status'] . "," . Config::get('custom_config.SUBCRIPTION_STATUS')['downgrade'];;
                } else {
                    $append_meta_arr['status'] = Config::get('custom_config.SUBCRIPTION_STATUS')['downgrade'];
                }


            }

            $objSubcripton->metadata = $append_meta_arr;
            $objSubcripton->save();

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }
        return $retArr;
    }

    /**subcriptionmeta detail
     * @param $custId
     * @return Exception|\Exception|Error\ApiConnection|Error\Authentication|Error\Base|Error\InvalidRequest
     */
    public function getCustSubcriptionMetaDetailsById($custId) {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $userId = 0;
        try {
            $symbolString = "";
            $customerObj = \Stripe\Customer::retrieve($custId);
            if (isset($customerObj->email)) {

                $userObj = User::where("email", $customerObj->email)->select("id")->first();
                $userId = $userObj->id;

            }

            $subcriptionId = $this->getStripeSubcriptionId($userId);
            $objSubcripton = \Stripe\Subscription::retrieve($subcriptionId);
            $retArr['start_date'] = date('m-d-Y', $objSubcripton->current_period_start);
            $retArr['end_date'] = date('m-d-Y', $objSubcripton->current_period_end);
            $retArr['id'] = $objSubcripton->id;


            if ($objSubcripton->metadata) {

                if (isset($objSubcripton->metadata['basic_plan'])) {

                    $symbolString .= $objSubcripton->metadata['basic_plan'];

                }
                if (isset($objSubcripton->metadata['additional_plan'])) {

                    if (!empty($symbolString)) {
                        $symbolString .= ",";
                    }
                    $symbolString .= $objSubcripton->metadata['additional_plan'];
                }
                $retArr['symbol'] = (explode(",", $symbolString));
                if (isset($objSubcripton->metadata['canceled'])) {
                    $retArr['canceled'] = explode(',', $objSubcripton->metadata['canceled']);

                } else {
                    $retArr['canceled'] = [];
                }

            }

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (Exception $exception) {
            return $exception;
        }
        return $retArr;

    }

    /**
     * update default card
     * @param $customerId
     * @param $cardId
     */
    public function setDefaultCard($customerId, $sourceId) {

        try {

            $customer = \Stripe\Customer::retrieve($customerId);
            $customer->default_source = $sourceId;
            $customer->save();
            return $sourceId;

        } catch (\Stripe\Error\Card $exception) {

            $retArr = $this->errorMessage($exception);
            return $retArr;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
            return $retArr;
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

    }

    /**
     * get subcription by id
     * @param $custId
     * @return Exception|bool|\Exception|mixed|Error\ApiConnection|Error\Authentication|Error\Base|Error\InvalidRequest
     */
    public function getSubcriptionPaymentDateById($custId) {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['next_payment_date'] = '';
        $userId = 0;
        try {

            $customerObj = \Stripe\Customer::retrieve($custId);
            if (isset($customerObj->email)) {

                $userObj = User::where("email", $customerObj->email)->select("id")->first();
                $userId = $userObj->id;

            }
            $subcriptionId = $this->getStripeSubcriptionId($userId);
            $objSubcripton = \Stripe\Subscription::retrieve($subcriptionId);


            if (!empty($objSubcripton)) {
                $retArr['next_payment_date'] = date('m-d-Y', $objSubcripton->current_period_end);
                return $retArr;
            }
            return false;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

        return $retArr;

    }

    /**
     * get subcription plan info
     * @param $subscriptionId
     * @return Exception|\Exception|mixed|Error\ApiConnection|Error\Authentication|Error\Base|Error\InvalidRequest
     */
    public function getSubcriptionPlanInfo($subscriptionId) {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['plan_name'] = '';
        $retArr['plan_id'] = '';

        try {

            $objSubcripton = \Stripe\Subscription::retrieve($subscriptionId);
            foreach ($objSubcripton->items['data'] as $rows) {

                if ($rows->plan['id'] != Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY')) {

                    $retArr['plan_name'] = $rows->plan->nickname;
                    $retArr['plan_id'] = $rows->plan->id;
                }

            }

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (Exception $exception) {
            return $this->errorDefaultMessage($exception->getMessage());
        }

        return $retArr;


    }

    /**
     * get subcription plan from modulesubcriptontrack
     * @param string $id
     * @return int
     */
    public function getStripeSubcriptionId($id = '') {

        $subcriptionId = 0;
        $objModuleSubcriptionTrakcer = ModuleSubscriptionTracker::where("user_id", $id)->where("subscription_status","!=",
            Config::get('custom_config.MODULE_SUBCRIPTION_TRACKERS_STATUS')['cancel'])->first();
        if (!empty($objModuleSubcriptionTrakcer)) {
            $subcriptionId = $objModuleSubcriptionTrakcer->subscription_id;
        }
        return $subcriptionId;

    }

    /**
     * @param $email
     * @return Exception|\Exception|Error\ApiConnection|Error\Authentication|Error\Base|Error\InvalidRequest
     */
    public function getProrationAmountBase($email,$plan) {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['prorate_amount'] = '';
        $retArr['amount_remaining'] = '';
        $retArr['interval'] = '';
        $retArr['amount'] = 0;
        $retArr['nickname'] = '';
        $quantity = 1;
        $subscriptionItem = '';
        $planId = 0;
        $items = [];

        try {
            $customer_id = $this->getCustomerId($email);
            $subcription_id = $this->getStripeSubcriptionId(Auth::user()->id);
            $subscription = \Stripe\Subscription::retrieve($subcription_id);
            $subscriptionId = $subscription->id;

            $items = [
                [
                    'plan' => $plan,
                    'quantity' => 1,
                ],
            ];

            $proration_date = time();

            $invoice = \Stripe\Invoice::upcoming([
                'customer' => $customer_id,
                'subscription' => $subscriptionId,
                'subscription_items' => $items,

                'subscription_proration_date' => $proration_date,
                'subscription_prorate' => true

            ]);


            // Calculate the proration cost:
            $cost = 0;
            $current_prorations = [];
            foreach ($invoice->lines->data as $line) {

                if ($line->period->start == $proration_date) {
                    array_push($current_prorations, $line);
                    $cost += $line->amount;
                }
            }

            $retArr['prorate_amount'] = $cost/100 ?? 0;
            $retArr['amount_remaining'] = $invoice['amount_remaining'] ?? 0;


        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);

        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);

        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);

        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);

        } catch (Exception $exception) {
            return $exception;
        }

        return $retArr;
    }

    public function getPlanById($planId) {

        $retArr['code'] = '200';
        $retArr['message'] = '';
        $retArr['amount'] = '';

        try {
            $plan = \Stripe\Plan::retrieve($planId);
            $retArr['amount'] = $plan->amount/100 ?? '0.00';

            return $retArr;

        } catch (\Stripe\Error\InvalidRequest $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Authentication $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\ApiConnection $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (\Stripe\Error\Base $exception) {
            $retArr = $this->errorMessage($exception);
        } catch (Exception $exception) {
            return $exception;
        }

    }




}