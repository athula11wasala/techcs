<?php

namespace App\Http\Controllers;

use App\Equio\Helper;
use App\Models\User;
use App\Models\UserProfile;
use App\Repositories\PasswordResetsRepository;
use App\Services\RoleUserService;
use App\Services\UserService;
use App\Services\WooCommerceService;
use App\Traits\UserValidators;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use App\Traits\UserProfileValidate;
use Illuminate\Support\Facades\DB;
use Automattic\WooCommerce\Client;
use App\Equio\Exceptions\EquioException;



class UsersController extends ApiController
{
    use UserValidators;
    use UserProfileValidate;

    /**
     * @var UserService
     */
    private $userService, $password_reset, $role_user, $wooCommerceService;
    private $error = 'error';
    private $message = 'message';

    /**
     * UsersController constructor.
     * @param UserService $userService
     * @param PasswordResetsRepository $passwordResetsRepository
     */
    public function __construct(UserService $userService, PasswordResetsRepository $passwordResetsRepository,
                                RoleUserService $roleUserService, WooCommerceService $wooCommerceService)
    {
        $this->middleware ( 'auth:api' );

        $this->userService = $userService;
        $this->password_reset = $passwordResetsRepository;
        $this->role_user = $roleUserService;
        $this->wooCommerceService = new WooCommerceService();
    }

    public function index(Request $request)
    {
        $allUsersCount = $this->userService->countUsersByFilters ( $request, '' );
        $inTrialUsersCount = $this->userService->countUsersByFilters ( $request, 'inTrial' );
        $inPaidUsersCount = $this->userService->countUsersByFilters ( $request, 'paid' );
        $expiredUsersCount = $this->userService->countUsersByFilters ( $request, 'expired' );
        $disabledUsersCount = $this->userService->countUsersByFilters ( $request, 'disabled' );
        $othersUsersCount = $this->userService->countUsersByFilters ( $request, 'other' );

        $users = $this->userService->allUsers ( $request );

        $users->put ( 'all_users', $allUsersCount );
        $users->put ( 'trial_users', $inTrialUsersCount );
        $users->put ( 'paid_users', $inPaidUsersCount );
        $users->put ( 'expired_users', $expiredUsersCount );
        $users->put ( 'disable_users', $disabledUsersCount );
        $users->put ( 'other_users', $othersUsersCount );

        return $this->respond ( $users );
    }

    public function assignRoleUser($userId, $role)
    {
        $user = User::find ( $userId );
        switch ($role) {
            case 1:
                $role = $this->role_user->getRoleByName ( 'ADMINISTRATOR' );
                break;
            case 2:
                $role = $this->role_user->getRoleByName ( 'EQUIO' );
                break;
            case 3:
                $role = $this->role_user->getRoleByName ( 'MANAGER' );

                break;
            case 4:
                $role = $this->role_user->getRoleByName ( 'EDITOR' );
                break;
            case 5:
                $role = $this->role_user->getRoleByName ( 'REPORTER' );
                break;
            case 6:
                $role = $this->role_user->getRoleByName ( 'OPERATOR' );
                break;

            default:

        }
        $user->attachRole ( $role );

    }


    public function addNewUser(Request $request)
    {
        $validator = $this->validateAddUser ( $request->all () );

        if ( $validator->fails () ) {
            return response ()->json ( [$this->error => $validator->errors ()->first ()], 400 );
        }

        try {
            \DB::beginTransaction ();

            $randomString = Helper::randomString ();

            $paidsubscription_start = date ( 'Y-m-d' );
            $subscription_length = !empty($request->subscription_length)?$request->subscription_length : 0;
            $paid_subscription_end = date ( 'Y-m-d', strtotime ( ' +' . $subscription_length . 'days' ) );

            $user_array = [
                'email' => $request->email,
                'subscription_level' => $request->subscription_level,
                'trial' => $request->is_trial,
                'trial_period' => $request->trail_period,
                'role' => (int)$request->role,
                'password' => Hash::make ( $randomString ),
                'encrypted_password' => md5 ( $randomString ),
                'trial_datetime'=>$paidsubscription_start,
                'paid_subscription_start'=>$paidsubscription_start,
                'paid_subscription_end'=>$paid_subscription_end,
                'subscription_renewal'=>$request->auto_renew
            ];

            $country_id = 0;

         /*   if(isset($request->country)){

                $country_data = DB::table("countries")->where("name",$request->country)->select("name","id")->first();

                if(isset($country_data->id)){

                    $country_id = $country_data->id;
                }else {

                    $country_id = $request->country;
                }

            }
           */


           $state_data = DB::table("states")->where("name",$request->state)->select("name","id")->first();
           $user = $this->userService->createUser ( $user_array );

            $user_profile_array = [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone,
                'country' =>  $request->country,
                'state_id' => !empty($state_data->id)?  $state_data->id : '',
                'state' =>  !empty($state_data->name)?  $state_data->name : $request->state,
                'city' => $request->city,
                'zip' => $request->zip,
                'street_address1' => $request->street,
                'street_address2' => $request->street2,
            ];

            $user_profile = $this->userService->createUserProfile ( $user_profile_array );

            $this->assignRoleUser ( $user->id, $user->role );
            $token = $this->randomConfirmationCode ();
            $data = ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now ()];
            $this->password_reset->create ( $data );
            $this->sendResetToken ( $request, $token );

            \DB::commit ();

            return response ()->json ( [$this->message => __ ( 'messages.user_add_success' )], 200 );
        } catch (Exception $e) {
            \DB::rollBack ();
            return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
        }
    }

    private function randomConfirmationCode()
    {

        $id = str_random ( 30 );
        $validator = \Validator::make ( ['id' => $id], ['id' => 'unique:password_resets,token'] );
        if ( $validator->fails () ) {
            $this->randomConfirmationCode ();
        }

        return $id;
    }

    private function sendResetToken($user, $token)
    {

        $name = $user->first_name . ' ' . $user->last_name;
        $data = ['from' => Config::get ( 'custom_config.from_email' ),
            'system' => Config::get ( 'custom_config.system_email_send' )];
        $subject = Lang::get ( 'email_subjects.Change_password' );

        try {
            Mail::send ( 'emails.forotPassword', array('name' => $name, 'confirmationCode' => $token),
                function ($message) use ($user, $name, $subject, $data) {
                    $message->from ( $data[ 'from' ], $data[ 'system' ] );
                    $message->to ( $user->email, $name )->subject ( $subject );

                } );
        } catch (\Swift_TransportException $ex) {
            print_r( $ex->getMessage () );
            return false;
        } catch (\Swift_RfcComplianceException $ex) {
            print_r ( $ex->getMessage () );
            return false;
        }

        if ( count ( Mail::failures () ) > 0 ) {
            return false;
        } else {
            return true;
        }

    }

    public function show(Request $request, $id)
    {
        $userObject = $this->userService->getBasicInfoByUserId ( $id );
        \Log::info ( "==== user ", ['u' => $userObject] );

        return $this->respond ( $userObject );
    }

    public function currentUser(Request $request)
    {
        $user = Auth::user ();
        $userObject = $this->userService->getBasicInfoByUserId ( $user->id );

        return $this->respond ( $userObject );
    }


    public function updateUserProfile(Request $request, $id)
    {
        $validator = $this->validateUpdateUser ( $request->all () );
        $requestData = $request->all ();
        if(isset($requestData['state_id'])){
                $state_name = DB::table("states")->where("name",$requestData['state_id'])->select("id")->first();
                if(isset($state_name->id)){

                    $requestData['state_id']  =  $state_name->id;

                }
        }
        $userUpdateProperties = [
            'disable', 'trial', 'trial_period', 'subscription_level', 'subscription_renewal'
        ];

        $userProfileUpdateProperties = [
            'first_name', 'middle_name', 'last_name', 'phone_number', 'country', 'state',
            'city', 'zip', 'state_id', 'address', 'street_address1', 'street_address2'
        ];

        if ( $validator->fails () ) {
            return response ()->json ( [$this->error => $validator->errors ()->first ()], 400 );
        }

        $userObject = $this->userService->getBasicInfoByUserId ( $id );

        if ( !isset( $userObject ) ) {
            return response ()->json ( [$this->error => __ ( 'messages.no_user_exit' )], 400 );
        }



        $userArray = Helper::filterArrayValuesByKeys ( $requestData, $userUpdateProperties );


        \Log::info ( "===== filtered user array ", ['userarray' => $userArray] );

        $userProfileArray = Helper::filterArrayValuesByKeys ( $requestData, $userProfileUpdateProperties );
        \Log::info ( "===== filtered profile array ", ['u' => $userProfileArray] );

        if ( empty( $userArray ) && empty( $userProfileArray ) ) {
            \Log::info ( "==== user properties and user profiles properties are empty!" );
            return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
        }

        try {
            \DB::beginTransaction ();

            if ( count ($userArray)>0 ) {

                $user = $this->userService->updateUser ( $userArray, $id, !empty( $requestData[ 'roles' ] ) ? $requestData[ 'roles' ] : null, !empty( $requestData[ 'paid_subscription_end' ] ) ? $requestData[ 'paid_subscription_end' ] : null,
                    !empty( $requestData[ 'paid_subscription_start' ] ) ? $requestData[ 'paid_subscription_start' ] : null );

            \Log::info ( "==== user updated status", ['woosub' => $user] );
            if ( isset( $user ) ) {

                \Log::info ( "==== user properties updated successfully!" );
                // update woocommerce subscription
                $subscriptionIds = [];
                // set user email
                $subscriptionData = ['email' => $userObject[ 'email' ]];
                // get available subscription plans by email
                $availableSubscriptions = $this->wooCommerceService->retrieveWooData ( 'subscriptions/', $subscriptionData );
//                print_r($availableSubscriptions);die;
                // check the user request of auto renewal
                if ( $userArray[ 'subscription_renewal' ] == 'n' ) {
                    foreach ( $availableSubscriptions as $availableSubscription ) {
                        if ( $availableSubscription->status == 'active' ) {
                            $subscriptionIds[] = $availableSubscription->id;
                        } else {
                            continue;
                        }
                    }
                    // if auto renewal n get all active subscription ids
                    $subscriptionStatus = ['status' => 'cancelled'];
                } else if ( $userArray[ 'subscription_renewal' ] == 'y' ) {
                    foreach ( $availableSubscriptions as $availableSubscription ) {
                        if ( $availableSubscription->status == 'cancelled' ) {
                            $subscriptionIds[] = $availableSubscription->id;
                        } else {
                            continue;
                        }
                    }
                    // if auto renewal y get all cancelled subscription ids
                    $subscriptionStatus = ['status' => 'active'];
                }

                // updating subscription status
                foreach ( $subscriptionIds as $subscriptionId ) {
                    \Log::info ( "==== woo subscription status updating", ['woosub' => $subscriptionId . "->" . $subscriptionStatus[ 'status' ]] );
                    $this->wooCommerceService->updateWooData ( 'subscriptions/' . $subscriptionId, $subscriptionStatus );
                }
            }
        }
            if ( !empty( $userProfileArray ) )
                $this->userService->updateUserProfile ( $userProfileArray, $id );

            if ( !empty( $requestData[ 'roles' ] ) && $id != null )
                $this->userService->updateUserRoles ( $requestData[ 'roles' ], $id );


            \DB::commit ();

            return response ()->json ( [$this->message => __ ( 'messages.user_profile_updated_success' )], 200 );
        } catch (Exception $e) {
            \DB::rollBack ();
            return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
        }
    }

    public function getAllInterstDetail()
    {

        $interstData = $this->userService->getAllInterestInfo ();

        return $this->respond ( $interstData );
    }

    public function getPersonalDetails()
    {

        $personalInfo = $this->userService->getPersonalInfo ( Auth::user ()->id );

        if ( $personalInfo ) {
            return response ()->json ( ['data' => $personalInfo, 'user_postion' => Helper::userPositionInfo (),
                'industryInfo' => Helper::industryInfo (),
                'companyyInfo' => Helper::CompanyHeaderInfo (),
            ], 200 );
        }

        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function updatePersonalDetails(Request $request)
    {

        $validator = $this->userProfileValidate ( $request->all (), 'PUT' );

        if ( $validator->fails () ) {

            return response ()->json ( [$this->error => $validator->errors ()], 400 );
        }

        if ( $validator->passes () ) {

            if(isset($request['state'])){
                $state_name = DB::table("states")->where("name",$request['state'])->select("id")->first();
                if(isset($state_name->id)){

                    $request['state']  =  $state_name->id;

                }
            }


            $profileData = $this->userService->updateUserPersonalInfo ( $request, Auth::user ()->id );

            if ( $profileData ) {
                return response ()->json ( [$this->message => __ ( 'messages.user_profile_updated_succes' )], 200 );
            }

            return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );

        }


    }


    public function addInterestToUser(Request $request)
    {

        $interstData = $this->userService->AddInterestUser ( $request->all () );

        if ( $interstData ) {
            return response ()->json ( [$this->message => __ ( 'messages.user_interest_add_success' )], 200 );
        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );

    }


    public function viewInterestToUser(Request $request)
    {
        $userId = Auth::user ()->id;
        $interstData = $this->userService->viewInterestUser ( $userId );
        if ( $interstData ) {
            return response ()->json ( ['data' => $interstData], 200 );
        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function getOrderDetails($email)
    {

        try {
            $has_trial = false;
            $billing_period = "";
            $next_payment_date = "";
            $this->woocommerce = new Client(Config::get('custom_config.WOOCOMMERCE_API_URL'), Config::get('custom_config.WOOCOMMERCE_API_KEY'), Config::get('custom_config.WOOCOMMERCE_API_SECRET'), ['wp_api' => true, 'version' => 'wc/v1',]);
            $orders = $this->woocommerce->get('orders', ['search' => $email, 'per_page' => 100]);
            $purchasedItem = [];
            foreach ($orders as $order) {
                \Log::info("==== UsersController->getOrderDetails ", ['u' => json_encode($order)]);
                foreach ($order->line_items as $lineItem) {
                    $purchasedItem[]   = $lineItem->product_id;
                }
            }
            $this->woocommerce = new Client(Config::get('custom_config.WOOCOMMERCE_API_URL'), Config::get('custom_config.WOOCOMMERCE_API_KEY'), Config::get('custom_config.WOOCOMMERCE_API_SECRET'), ['wp_api' => true, 'version' => 'wc/v1',]);
            $subscriptions = $this->woocommerce->get('subscriptions', ['search' => $email, 'per_page' => 50,'order'=>'asc']);

            foreach ($subscriptions as $subscription) {
                \Log::info("==== UsersController->getOrderDetails ", ['u' => json_encode($subscription)]);
                if($subscription->status=="active" && property_exists($subscription, 'trial_end_date') === true &&
                    !empty($subscription->trial_end_date)) {
                    $has_trial = true;
                }
                if($subscription->status=="active" && property_exists($subscription, 'billing_period') === true &&
                    !empty($subscription->billing_period)) {
                    $billing_period = $subscription->billing_period;
                }
                if($subscription->status=="active" && property_exists($subscription, 'next_payment_date') === true &&
                    !empty($subscription->next_payment_date)) {
                    $next_payment_date = $subscription->next_payment_date;
                }
                foreach ($subscription->line_items as $lineItem) {
                    $purchasedItem[]   = $lineItem->product_id;
                }
            }
            return array($purchasedItem, $has_trial, $billing_period, $next_payment_date);

        } catch (HttpClientException $e) {
            throw new EquioException($e->getMessage());
        } catch (\Exception $e) {
            throw new EquioException($e->getMessage());
        }
    }


    public function getSubscriptionDetails()
    {
        $userId = Auth::user ()->id;
        $subscriptionData = $this->userService->viewSubscriptionUserDetail ( $userId );
        list($purchased_reports , $has_trial, $billing_period, $next_payment_date) = $this->getOrderDetails (Auth::user ()->email);
        $check_subcription_plan = 18046;
        $subscriptionData['subscription_plan_purchased'] = false;
        if (in_array($check_subcription_plan, $purchased_reports)){
        
            $subscriptionData['subscription_plan_purchased'] = true;
        }
        $subscriptionData['has_trial'] = $has_trial;
        $subscriptionData['billing_period'] = $billing_period;
        $subscriptionData['renewal_date'] = $next_payment_date;

        if ( $subscriptionData ) {

            return response ()->json ( ['data' => $subscriptionData], 200 );

        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function getSubscriptionChange(Request $request)
    {
        $userId = Auth::user ()->id;
        $subscriptionPlanData = $this->userService->changeSubscriptionPalnUser ( $userId, $request->all () );
        if ( $subscriptionPlanData ) {
            return response ()->json ( ['data' => Lang::get ( 'messages.update_success' ),'trail_status'=>$subscriptionPlanData['trail_status']], 200 );
        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function getCheckSubscriptionChange(Request $request)
    {
        $userId = Auth::user ()->id;

        $checkIsCancel = $this->userService->checkSubscriptionChange ( $userId, $request->all () );

        return response ()->json ( ['subscription_plan'=>$checkIsCancel], 200 );
    }


    public function getWebinarDetails(Request $request)
    {
        $webinarData = $this->userService->webinars ( $request->all () );
        if ( $webinarData ) {

            return response ()->json ( ['data' => $webinarData], 200 );
        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function getCannaClipsDetails(Request $request)
    {

        $cannaClipsData = $this->userService->cannaClips ( $request->all () );

        if ( $cannaClipsData ) {

            return response ()->json ( ['data' => $cannaClipsData], 200 );

        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function getPersentionDeckDetails(Request $request)
    {

        $presntionDeckData = $this->userService->presentationDeck ( $request->all () );

        if ( $presntionDeckData ) {

            return response ()->json ( ['data' => $presntionDeckData], 200 );

        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function getPermentRecordInfo(Request $request)
    {
        $paymentRecordData = $this->userService->paymentRecordInfo ( $request->all () );
        if ( $paymentRecordData ) {
            return response ()->json ( ['data' => $paymentRecordData], 200 );
        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
    }


    public function updateChangePassword(Request $request)
    {
        $validator = $this->userProfileValidate ( $request->all (), 'ChangePassword' );
        if ( $validator->fails () ) {
            return response ()->json ( [$this->error => $validator->errors ()], 400 );
        }
        if ( $validator->passes () ) {
            $data = $this->userService->UserChangePassword ( $request );
            if ( $data ) {
                return response ()->json ( ['data' => Lang::get ( 'messages.change_password' )], 200 );
            }
            return response ()->json ( [$this->error => Lang::get ( 'messages.un_processable_request' )], 400 );
        }
    }


    public function getFeedBack(Request $request)
    {
        $data = $this->userService->userFeedBackMail ( $request );
        if ( $data ) {
            return response ()->json ( ['data' => Lang::get ( 'messages.mail_send' )], 200 );
        }
        return response ()->json ( [$this->error => Lang::get ( 'messages.un_processable_request' )], 400 );
    }

    public function getRoleUserDetail(Request $request)
    {
        $data = $this->role_user->getRoleNameAndId ();
        if ( $data ) {
            return response ()->json ( ['data' => $data], 200 );
        }
        return response ()->json ( [$this->error => Lang::get ( 'messages.un_processable_request' )], 400 );
    }

    public function updateacceptTou(Request $request)
    {
        $data = $this->userService->updateAcceptTou ( $request->all () );
        if ( $data ) {
            return response ()->json ( [$this->message => __ ( 'messages.update_success' )], 200 );
        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function updateSignInCount(Request $request)
    {
        $data = $this->userService->updateSignInCount ( $request->all () );
        if ( $data ) {
            return response ()->json ( [$this->message => __ ( 'messages.update_success' )], 200 );
        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function getPaymentRecordWooInfo(Request $request)
    {
        $objHelper = new Helper();
        $data = $objHelper->wooComercePurchseHistoryInfo();
        if ( $data ) {
            return response ()->json ( ['data' => $data], 200 );
        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function getShortTrackerList(Request $request)
    {
        $data = $this->userService->getShortTrackerList();
        return response ()->json ( ['data' => $data], 200 );
    }

}



