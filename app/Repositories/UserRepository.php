<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Models\UserProfile;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use DateTime;

class UserRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\User';
    }

    /**
     * Returns basic user info by email
     * @param $email
     * @return mixed
     */
    public function basicInfoByEmail($email)
    {
        return $this->model->where ( 'email', '=', $email )
            ->select (
                [
                    'users.id', 'users.email', 'users.password',
                    'users.current_sign_in_at', 'users.current_sign_in_ip', 'users.sign_in_count',
                    'user_profiles.first_name', 'user_profiles.last_name'
                ] )
            ->join ( 'user_profiles', 'user_profiles.user_id', '=', 'users.id' )->first ();
    }

    public function updatePassword($email, $password)
    {
        return $this->model
            ->where ( 'email', '=', $email )
            ->update ( ['password' => bcrypt ( $password )] );
    }

    public function saveUser($user_array)
    {
        $user = $this->model->create ( $user_array );
        if ( $user ) {
            $user->role = $user_array[ 'role' ];
            $user->subscription_level = $user_array[ 'subscription_level' ];
            $user->trial_datetime = $user_array[ 'trial_datetime' ];
            $user->subscription_renewal = $user_array[ 'subscription_renewal' ];
            $user->save ();
        }

        return $user;
    }

    /**
     * Returns basic user info by user id
     * @param $id
     * @return mixed
     */
    public function basicInfoById($id)
    {
        $date_picker_option = false;
        $data = $this->model->where ( 'users.id', '=', $id )
            ->select (
                [
                    'users.id', 'users.email', 'users.password', 'users.subscription_level','users.paid_subscription_start','users.paid_subscription_end','users.subscription_renewal',
                    'users.trial', 'users.trial_period', 'users.disable',
                    'users.current_sign_in_at', 'users.current_sign_in_ip', 'users.sign_in_count', 'accept_tou',
                    'user_profiles.*', 'user_profiles.country as country_id', 'user_profiles.state as state_id'
                ]
            )
            ->join ( 'user_profiles', 'user_profiles.user_id', '=', 'users.id' )
            ->first ();

        $role = $this->model->where ( 'users.id', '=', $id )
            ->select (
                [
                    'role_user.role_id as id', 'roles.name as itemName'
                ] )
            ->join ( 'user_profiles', 'user_profiles.user_id', '=', 'users.id' )
            ->join ( 'role_user', 'user_profiles.user_id', '=', 'role_user.user_id' )
            ->join ( 'roles', 'roles.id', '=', 'role_user.role_id' )
            ->get ();
        $addCountry = Helper::getCountry ( $data );


        $date = new DateTime($data->paid_subscription_end);
        $now = new DateTime();

        if($date < $now) {
            $date_picker_option = true;
        }



        $user = collect ( $addCountry );
        if(isset($data->country_id))
        {
            $country_name = DB::table("countries")->where("id",$data->country_id)->select("name")->first();
            if(isset($country_name->name)){
                $user->put ( 'country_id', $country_name->name );

            }

        }
        if(isset($data->state_id))
        {
            $state_name = DB::table("states")->where("id",$data->state_id)->select("name")->first();
            if(isset($state_name->name)){
                $user->put ( 'state_id', $state_name->name );

            }

        }

        //

        $user->put ( 'role', $role );
        $user->put ( 'role', $role );
        $user->put ( 'trial_period', $data[ 'trial_period' ] );
        $user->put ( 'date_picker_option', $date_picker_option );

  //print_r($user); die();

        return $user;

    }

    /**
     * Count users by property
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function countUsersByProperty($attribute = null, $value = null)
    {

        \Log::info ( "===== count query begin" );
        $query = $this->model;

        if ( isset( $attribute ) && isset( $value ) ) {

            $query = $query->where ( $attribute, $value );
        }

        $count = $query->count ();
        \Log::info ( "===== count query end" );

        return $count;
    }


    public function countUsersDisableByProperty($attribute = null, $value = null)
    {

        \Log::info ( "===== count query begin" );
        $query = $this->model;

        if ( isset( $attribute ) && isset( $value ) ) {

            $query = $query->where ( $attribute, $value );
        }

        if ( $attribute != 'disable' ) {
            $query = $query->where ( 'disable', '0' );
        }

        $count = $query->count ();
        \Log::info ( "===== count query end" );

        return $count;
    }


    public function updateUser($user_array, $user_id, $role = null,$paid_subscription_end = null,$paid_subscription_start = null)
    {
        $paid_subscription_start = !empty($paid_subscription_start ) ? $paid_subscription_start : '';
        $paid_subscription_end = !empty($paid_subscription_end ) ? $paid_subscription_end : '';
        $trialPeriod = (!empty( $user_array[ 'trial_period' ] )) ? ($user_array[ 'trial_period' ]) : 0;
        $subscription_renewal = (!empty( $user_array[ 'subscription_renewal' ] )) ? ($user_array[ 'subscription_renewal' ]) : 'y';
        $disable = (!empty( $user_array[ 'disable' ] )) ? ($user_array[ 'disable' ]) : 0;
        $user = $this->update ( $user_array, $user_id, 'id' );
        $objUser = User::find ( $user_id );

        if(!empty($paid_subscription_end) && !empty($paid_subscription_start)){
            $objUser->paid_subscription_start =  date ( 'Y-m-d', strtotime ( $paid_subscription_start) );
            $objUser->paid_subscription_end =  date ( 'Y-m-d', strtotime ( $paid_subscription_end) );
            $objUser->is_cancel = 0;
            $objUser->no_renewal_reason = "";
            $objUser->subscription_renewal = $subscription_renewal;
            $objUser->save();

        }

        if ( !empty( $role ) ) {

            $objRole = $role;
            foreach ( $objRole as $role ) {

                if ( $role == 2 && $user_array[ 'subscription_level' ] ) {
                    $objUser->trial_period = $trialPeriod;
                    //when user is disabled. user table;s paidsubscription_end is not changed
                    //$objUser->paid_subscription_end = date ( 'Y-m-d', strtotime ( $objUser->trial_datetime . ' +' . $trialPeriod . 'days' ) );
                    // when user is disabled. user table;s paidsubscription_end is not changed */
                    if ( $disable == 1 ) {
                        $objUser->subscription_renewal = 'n';
                    } else {
                        $objUser->subscription_renewal = 'y';
                    }
                    $objUser->save ();

                }
            }
        }
        return $user;

    }


    /**
     * @param $email
     * @return mixed
     */
    public function userByEmail($email)
    {
        return $this->model->where ( 'users.email', '=', $email )
            ->select ( 'users.*' )->first ();
    }


    public function viewUserSubscription($userId)
    {
        $user = User::find ( $userId );
        $pruchaseDate = "";
        $startDate = "";
        $subscription_start_date = "";
        $endDate = "";
        $data = [];
        $allRoleIds = [];
        $trial_period_count = '';
        $allUserAssignRole = $user->roles ()->get ();
        $userDetail = $this->model->where ( 'users.id', '=', $userId )
            ->select ( 'users.subscription_level', 'users.subscription_level' )->first ();
        $data[ 'subscription_level' ] = $userDetail->subscription_level;
        $data[ 'trial_period_end_date' ] = '';
        $data[ 'trial_period_start_date' ] = '';
        $trial_period_count = '';
        $subscription_start_date = '';
        $trailPeriodEndDate = '';
        $renewl_date = "";
        $paid_subscription_end = '';

        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }

        if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {

            if ( $user->subscription_level == Config::get ( 'custom_config.PACKAGE_ESSENTIAL' ) ) {

                if ( $user->is_cancel == 1 ) {
                    $data[ 'cancellation_date' ] = !empty( $user->paid_subscription_end ) ? date ( 'm/d/Y', strtotime ( $user->paid_subscription_end ) ) : '';
                }
                $data[ 'trial_period_start_date' ] = !empty( $user->trial_datetime ) ? date ( 'm/d/Y', strtotime ( $user->trial_datetime ) ) : '';;

                //Check Trail Period User
                if ( (!empty( $user->paid_subscription_start ) ? date ( 'Y-m-d', strtotime ( $user->paid_subscription_start ) ) : '') ==
                    (!empty( $user->trial_datetime ) ? date ( 'Y-m-d', strtotime ( $user->trial_datetime ) ) : '') ) {
                    $subscription_start_date = date ( 'Y-m-d', strtotime ( ((!empty( $user->trial_datetime )) ? $user->trial_datetime : '') . ' + ' . (1 + ((!empty( $user->trial_period )) ? $user->trial_period : 0)) . 'days' ) );
                    $data[ 'trial_period_end_date' ] = date ( 'm/d/Y', (strtotime ( '-1 day', strtotime ( $subscription_start_date ) )) );
                    //$data[ 'trial_period_end_date' ] = date ( 'm/d/Y', strtotime ( $user->trial_datetime . ' + ' . (-1 + ((!empty( $user->trial_period )) ? $user->trial_period : 0)) . 'days' ) );
                    $trial_period_count = ((!empty( $user->trial_period )) ? $user->trial_period : 0) . ' days';

                } else {

                    $subscription_start_date = !empty( $user->paid_subscription_start ) ? date ( 'Y-m-d', strtotime ( $user->paid_subscription_start ) ) : '';
                    //$data[ 'trial_period_end_date' ] = date ( 'm/d/Y', strtotime ( $user->paid_subscription_start . ' - ' . (1) . 'days' ) );
                    $data[ 'trial_period_end_date' ] = date ( 'm/d/Y', strtotime ( $user->trial_datetime . ' + ' . (30) . 'days' ) );

                    if ( !empty( $user->paid_subscription_end ) ) {
                        $renewl_date = date ( 'Y-m-d', strtotime ( ((!empty( $user->paid_subscription_end )) ? $user->paid_subscription_end : 0) . ' + ' . 1 . 'days' ) );

                    }

                }


            } elseif ( $user->subscription_level == Config::get ( 'custom_config.PACKAGE_ENTERPRISE' ) ||
                $user->subscription_level ==  Config::get ( 'custom_config.PACKAGE_PREMIUMPLUS' ) || $user->subscription_level ==  Config::get ( 'custom_config.PACKAGE_PREMIUM' )
            ) {

                $subscription_start_date = (!empty( $user->paid_subscription_start ) ? date ( 'Y-m-d', strtotime ( $user->paid_subscription_start ) ) : '');;

                if ( (!empty( $user->paid_subscription_start ) ? date ( 'Y-m-d', strtotime ( $user->paid_subscription_start ) ) : '') !=
                    (!empty( $user->trial_datetime ) ? date ( 'Y-m-d', strtotime ( $user->trial_datetime ) ) : '') ) {

                    if ( !empty( $user->paid_subscription_end ) ) {

                        $renewl_date = date ( 'Y-m-d', strtotime ( ((!empty( $user->paid_subscription_end )) ? $user->paid_subscription_end : 0) . ' + ' . 1 . 'days' ) );

                    }


                }


            }

        }
        $data[ 'billing_period' ] = !empty( $user->billing_period ) ? $user->billing_period : '';
        $data[ 'trial_period' ] = !empty( $trial_period_count ) ? $trial_period_count : '';
        $data[ 'subscrption_start_date' ] = !empty( $subscription_start_date ) ? date ( 'm/d/Y', strtotime ( $subscription_start_date ) ) : '';
        $data[ 'renewal_date' ] = !empty( $renewl_date ) ? date ( 'm/d/Y', strtotime ( $renewl_date ) ) : '';

        return $data;
    }


    public function updateUserPassword($userId, $password)
    {
        return $this->model
            ->where ( 'id', '=', $userId )
            ->update ( ['password' => bcrypt ( $password )] );

    }

    public function UpdateAcceptTou($userId, $accepted_tou)
    {
        return $this->model
            ->where ( 'id', '=', $userId )
            ->update ( ['accept_tou' => $accepted_tou] );
    }


    public function UpdateSignInCount($userId, $decline)
    {
        $userSignCount = $this->model
            ->where ( 'id', '=', $userId )
            ->select ( 'sign_in_count' )->first ()[ 'sign_in_count' ];

        if ( !empty( $decline ) ) {

            if ( $decline == true ) {
                if ( ($userSignCount) == 1 ) {
                    return $this->model
                        ->where ( 'id', '=', $userId )
                        ->update ( ['sign_in_count' => 0] );
                    //accept_tou == y

                }

            }
        }
        return true;

    }


    public function cancelWoocomerce()
    {
        $woocommerce = new Client( Config::get ( 'custom_config.WOOCOMMERCE_API_URL' ), Config::get ( 'custom_config.WOOCOMMERCE_API_KEY' ), Config::get ( 'custom_config.WOOCOMMERCE_API_SECRET' ), ['wp_api' => true, 'version' => 'wc/v1', 'query_string_auth' => true] );
        $email = Auth::user ()->email;

        try {
            $subscriptions = $woocommerce->get ( 'subscriptions', ['search' => $email] );
            $subscription = array();
            foreach ( $subscriptions as $subscription ) {
                foreach ( $subscription->line_items as $line_item ) {
                    if ( strpos ( $line_item->name, 'Subscription' ) !== false ) {
                        $url_target = "subscriptions/" . $subscription->id;
                        $subscription = $woocommerce->put ( $url_target, ['status' => 'cancelled'] );

                    }
                }
            }
            //    echo json_encode ( $subscription, JSON_PRETTY_PRINT ) . PHP_EOL;
        } catch (HttpClientException $e) {
            //var_dump ( $e->getMessage () ); // Error message.

        }


    }


    public function changeUserSubscriptionPlan($userId, $plan, $reason)
    {
        $trial_status = $this->model->select ( "subscription_renewal", "trial", "trial_period", "trial_datetime" )
            ->where ( "id", Auth::user ()->id )->first ();

        $currentDate = date ( "Y-m-d" );
        $user = User::find ( $userId );
        $trail = false;

        if ( (!empty( $user->paid_subscription_start ) ? date ( 'Y-m-d', strtotime ( $user->paid_subscription_start ) ) : '') ==
            (!empty( $user->trial_datetime ) ? date ( 'Y-m-d', strtotime ( $user->trial_datetime ) ) : '') ) {

            $trail = true;
            if(isset($user->reports_purchased)){

                if($user->reports_purchased == "y"){
                    $trail = false;
                }
            }
        }

        if ( !empty( $plan ) && ($plan != null) ) {
            $flag = false;

            $trialDateTime = !empty( $user->trial_datetime ) ? date ( 'Y-m-d', strtotime ( $user->trial_datetime ) ) : '';
            $paidSubscriptStart = !empty( $user->paid_subscription_start ) ? date ( 'Y-m-d', strtotime ( $user->paid_subscription_start ) ) : '';


            if ( $plan == "n" ) {

                if ( $trialDateTime == $paidSubscriptStart ) {

                    $this->model
                        ->where ( 'id', '=', $userId )
                        ->update ( ['is_cancel' => 1,
                                'subscription_renewal'=>'n',
                                'no_renewal_reason' => (!empty( $reason )) ? ($reason) : null,
                                'subscription_renewal'=>'n',
                                'paid_subscription_end' => date ( 'Y-m-d', strtotime ( $currentDate . ' -' . 1 . 'days' ) )]

                        );

                } else {

                    $this->model
                        ->where ( 'id', '=', $userId )
                        ->update ( ['is_cancel' => 1,
                                'subscription_renewal'=>'n',
                                'no_renewal_reason' => (!empty( $reason )) ? ($reason) : null]

                        );
                }

                $this->cancelWoocomerce ();

                return ['flag' => true, 'trail_status' => $trail];

            }

        }
        return null;

    }

    public function checkUserIsCancel($userId)
    {
        $user = User::find ( $userId );
        return $user->is_cancel;
    }

    public function changeUserSubscriptionPlan__($userId, $plan, $reason)
    {

        $trial_status = $this->model->select ( "subscription_renewal", "trial", "trial_period", "trial_datetime" )
            ->where ( "id", Auth::user ()->id )->first ();

        $diff = date_diff ( date_create ( date ( "Y-m-d H:i:s" ) ), date_create ( $trial_status->trial_datetime ) );
        $diff_in_days = floor ( $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h / 24 + $diff->i / 60 ) - 1;

        if ( !empty( $plan ) && ($plan != null) ) {
            $flag = false;
            //if ( $plan == "n" && $trial_status->subscription_renewal == "y" && $diff_in_days < $trial_status->trial_period ) {
            if ( $plan == "n" && $trial_status->subscription_renewal == "y" ) {
                /// Reduce trial period from " . ($trial_status->trial_period) . " to " . $diff_in_days );
                $newTrailPeriod = (!empty( $trial_status->trial_period ) ? intval ( $trial_status->trial_period ) : 0) - (!empty( $diff_in_days ) ? intval ( $diff_in_days ) : 0);
                $this->model
                    ->where ( 'id', '=', $userId )
                    ->update ( ['subscription_renewal' => $plan, 'no_renewal_reason' => (!empty( $reason )) ? ($reason) : null]
                    //  ->update ( ['trial_period' => $newTrailPeriod, 'subscription_renewal' => $plan,'no_renewal_reason'=>(!empty( $reason )) ? ($reason) : null]

                    );

                $this->cancelWoocomerce ();
                $flag = true;
            }

            if ( $plan == "n" ) {
                $this->cancelWoocomerce ();
                $this->model
                    ->where ( 'id', '=', $userId )
                    ->update ( ['subscription_renewal' => $plan]
                    );
                $flag = true;
            }
            return true;
        }
        return null;

    }

    public function personalInfo($userId)
    {
        $data = [];
        $userData = $this->model->where ( 'users.id', '=', $userId )
            ->select (
                [
                    'users.email', 'user_profiles.first_name', 'user_profiles.last_name',
                    'user_profiles.industry', 'user_profiles.phone_number', 'user_profiles.street_address1', 'user_profiles.street_address2',
                    'user_profiles.position', 'user_profiles.country', 'user_profiles.zip',
                    'user_profiles.industry_role', 'user_profiles.news_company_header', 'user_profiles.news_company_header as  news_company_header_id',
                    'user_profiles.news_compny_detail', 'user_profiles.news_compny_detail as  news_compny_detail_id', 'user_profiles.city',
                    'countries.id  as country_id', 'user_profiles.country  as country', 'user_profiles.state_id as  state_id', 'user_profiles.state as state',
                    'user_company_news_informaiton.name as news_company_user'

                ] )
            ->join ( 'user_profiles', 'user_profiles.user_id', '=', 'users.id' )
            ->leftjoin ( 'countries', 'user_profiles.country', '=', 'countries.id' )
            ->leftjoin ( 'states', 'user_profiles.state', '=', 'states.code' )
            ->leftjoin ( 'company_news_informaiton', 'company_news_informaiton.id', '=', 'user_profiles.news_compny_detail' )
            ->leftjoin ( 'user_company_news_informaiton', 'user_company_news_informaiton.user_id', '=', 'users.id' )
            ->first ();


        if(isset($userData->state_id))
        {
            $state_name = DB::table("states")->where("id",$userData->state_id)->select("name")->first();
            if(isset($state_name->name)){
                $data[ 'state' ]  =  $state_name->name;

            }

        }else{

            $data[ 'state' ] = $userData->state;
        }

        $data[ 'email' ] = $userData->email;
        $data[ 'first_name' ] = $userData->first_name;
        $data[ 'last_name' ] = $userData->last_name;
        $data[ 'tel' ] = $userData->phone_number;
        $data[ 'zip' ] = $userData->zip;
        $data[ 'address' ] = $userData->street_address1;
        $data[ 'address2' ] = $userData->street_address2;
        $data[ 'company' ] = $userData->industry;
        $data[ 'position' ] = $userData->position;
        $data[ 'country' ] = $userData->country;
        $data[ 'industry_role' ] = $userData->industry_role;
        $data[ 'news_company_header' ] = $userData->news_company_header;
        $data[ 'news_company_header_id' ] = $userData->news_company_header_id;
        $data[ 'news_compny_detail' ] = $userData->news_compny_detail;
        $data[ 'news_compny_detail_id' ] = $userData->news_compny_detail_id;
        $data[ 'country_id' ] = $userData->country_id;
        //$data[ 'state' ] = $userData->state;
        $data[ 'state_id' ] = $userData->state_id;
        $data[ 'city' ] = $userData->city;
        $objUserProfile = UserProfile::where ( "user_id", $userId )->select ( "industry_role", "position" )->first ();
        $data[ 'industry_role_id' ] = !empty( $objUserProfile->industry_role ) ? $objUserProfile->industry_role : null;
        $data[ 'position_id_id' ] = !empty( $objUserProfile->position ) ? $objUserProfile->position : null;
        $data['news_company_user'] = !empty($userData->news_company_user) ? $userData->news_company_user : '';

        \Log::info("==== UserRepository->personalInfo ", [$data]);
        return $data;

    }

    public function getNewsHeadrChange($header, $detail)
    {

        $objNewsHeader = Config::get ( "custom_config.COMPANY_NEWS_INFORMATION" )[ $header ];
        $objNewsDetail = DB::table ( "company_news_informaiton" )->select ( "name" )->where ( "id", $detail )->first ();

        if ( !empty( $header ) && $header == 3 ) {

            return 1;
        }
        if ( !empty( $header ) && $header == 6 ) {

            return 1;
        }
        if ( !empty( $header ) && $header == 7 ) {

            return 1;
        }

        if ( !empty( $objNewsDetail->name ) && $objNewsDetail->name == "Other conference (please specify)" ) {

            return 2;
        }
        if ( !empty( $objNewsDetail->name ) && $objNewsDetail->name == "Other (please specify)" ) {

            return 2;
        }

        return false;

    }


    public function updatePersonalInfo($data, $userId)
    {
        \Log::info("==== Update personalInfo ", [$data]);
        $this->model = new UserProfile();
        $newsStatus = 0;
        $position = !empty( $data->position ) ? $data->position : '';
        $state = !empty( $data->state ) ? $data->state : '';
        $country = !empty( $data->country ) ? $data->country : '';
        $newsCompanyDetail = !empty( $data->news_compny_detail ) ? $data->news_compny_detail : '';
        $newsCompanyHeader = !empty( $data->news_company_header ) ? $data->news_company_header : '';
        $newsCompanyUser = !empty( $data->news_company_user ) ? $data->news_company_user : '';
        $industryRole = !empty( $data->industry_role ) ? $data->industry_role : '';
        $zip = !empty( $data->zip ) ? $data->zip : '';
        $city = !empty( $data->city ) ? $data->city : '';

        if ( $this->getNewsHeadrChange ( $newsCompanyHeader, $newsCompanyDetail ) == false ) {

            DB::table ( "user_company_news_informaiton" )->where ( "user_id", Auth::user ()->id )->delete ();

        } else {

            DB::table ( "user_company_news_informaiton" )->where ( "user_id", Auth::user ()->id )->delete ();
            $type = $this->getNewsHeadrChange ( $newsCompanyHeader, $newsCompanyDetail );

            DB::table ( 'user_company_news_informaiton' )->insert (
                ['user_id' => Auth::user ()->id, 'type' => $type, 'name' => $newsCompanyUser]
            );
        }

        if ( !empty( $data->news_status ) ) {

            if ( $data->news_status == true ) {
                $newsStatus = 1;
            } else {
                $newsStatus = 0;
            }

        }

        return $this->model
            ->where ( 'user_id', '=', $userId )
            ->update ( ['first_name' => $data[ 'first_name' ],
                'last_name' => $data[ 'last_name' ],
                'phone_number' => $data[ 'phone_number' ],
                'street_address1' => $data[ 'address' ],
                'street_address2' => !empty( $data[ 'address2' ] ) ? $data[ 'address2' ] : '',
                'city' => $city,
                'industry' => $data[ 'company' ],
                'news_status' => $newsStatus,
                'industry_role' => $industryRole,
                'news_company_header' => $newsCompanyHeader,
                'news_compny_detail' => $newsCompanyDetail,
                'position' => $position,
                'country' => $country,
                'zip' => $zip,
                'state_id' => $state] );
    }


    public function usersAllInfo($request)
    {
        $this->perPage = (!empty( $request[ 'page' ] )) ? ($request[ 'page' ]) : env ( 'PAGINATE_PER_PAGE', 15 );
        $sort = ($request->sort) ? $request->sort : 'asc';
        $sortColumn = ($request->sortType) ? $request->sortType : 'users.id';
        $orderBy = "order by " . $sortColumn . " " . $sort;
        $where = "where 1=1 ";


        $sql = "select users.*, ( CASE WHEN DATE(users.paid_subscription_start) = DATE(users.trial_datetime) && DATE(paid_subscription_start) <= CURDATE()
                                         && (paid_subscription_end) >= CURDATE() && DISABLE = '0' THEN 'in trial' 
                                 WHEN disable ='0' && trial_datetime != paid_subscription_start && paid_subscription_start <= CURDATE() && paid_subscription_end >= CURDATE() then 'paid' 
                                 when paid_subscription_end <= CURDATE() && disable = '0' then 'expired' when disable = '1' then 'disabled' 
                                 ELSE '-' END) AS status, 
               (CASE users.role WHEN users.role = 1 THEN 'Admin' ELSE 'User' END) AS role_state,
               DATE_FORMAT(users.created_at,'%m/%d/%Y') as created_date,  DATE_FORMAT(users.last_sign_in_at,'%m/%d/%Y') as last_signed_in_date,
               (CASE WHEN users.subscription_renewal = 'y'  THEN  date(DATE_ADD(trial_datetime, INTERVAL  (365 + trial_period) day)) 
               ELSE '-' END) AS renewal_date    from `users` left join `user_profiles` on 
               user_profiles.user_id = users.id  ";

        if ( $request->email ) {

            $where = $where . ' and users.email like % ' . $request->email;
        }

        if ( $request->first_name ) {

            $where = $where . ' and user_profiles.first_name like % ' . $request->first_name;
        }

        if ( $request->last_name ) {

            $where = $where . ' and user_profiles.last_name like % ' . $request->last_name;
        }

        if ( $request->filterRole ) {

            if ( $request->filterRole == 1 && !empty( $request->filterSubscription ) ) {
            } else {

                $user_ids_result = RoleUser::where ( 'role_id', $request->filterRole )
                    ->select ( 'user_id' )
                    ->get ();
                foreach ( $user_ids_result as $row ) {

                    $user_id[] = $row->user_id;
                }
                $user_ids = implode ( "', '", $user_id );

                $where = $where . " and users.id in '" . $user_ids . "' ";

            }

        }

        if ( $request->filterSubscription ) {

            if ( $request->filterRole == 2 ) {
                $where = $where . ' and users.subscription_level = ' . $request->filterSubscription;

            }

        }

        if ( $request->paymentStatus ) {

            $where = $where . ' and users.payment_status = ' . (INT)$request->paymentStatus;

        }

        if ( isset( $request->disabled ) ) {

            if ( empty( $request->disabled ) ) {

                $where = $where . " and users.disable = '0' ";
            } else {
                $where = $where . " and users.disable = '1' ";
            }
        }

        $sql = $sql . $where;

        if ( $request->renewal_year ) {
            $sql = $sql . "having Year(renewal_date) =  $request->renewal_year ";

        }

        if ( $request->renewal_year_month != 0 ) {
            if ( false ) {
                $sql = $sql . " having Year(renewal_date) = '" . substr ( $request->renewal_year_month, -4 ) . "'";
                $sql = $sql . " and  Month(renewal_date) = '" . substr ( $request->renewal_year_month, 0, 2 ) . "' ";

            }
        }

        $sql = $sql . " " . $orderBy;
        $results = DB::select ( $sql );
        return $results;
    }


    /**
     * Count users by filter
     * @param array $request
     * @param array $attributes
     * @return mixed
     */
    public function countUsersByFilters($request = array(), $type)
    {
        $query = $this->model;

        if ( $request->filterRole ) {
            if ( $request->filterRole == 1 && !empty( $request->filterSubscription ) ) {

            } else {
                $query = $query->where ( 'role', $request->filterRole );
            }
        }

        if ( $request->queryString ) {
           $this->keywordSearch ( $request->queryString );
        }

        if ( $request->filterSubscription ) {
            if ( $request->filterRole == 2 ) {
                $query = $query->where ( 'subscription_level', $request->filterSubscription );
            }
        }

        if ( !empty( $type ) ) {
            switch ($type) {
                case 'inTrial':

                    $now = date ( 'Y-m-d H:i:s' );
                    $query = $query->whereRaw ( "Date(users.paid_subscription_start)= Date(users.trial_datetime)" );
                    $query = $query->whereRaw ( "(paid_subscription_start)<= '" . $now . "'" );
                    $query = $query->whereRaw ( "(paid_subscription_end)>= '" . $now . "'" );
                    $query = $query->where ( 'disable', '=', "0" );
                    $query->join ( 'user_profiles', 'user_profiles.user_id', '=', 'users.id' );

                    break;
                case 'paid':
                    $now = date ( 'Y-m-d H:i:s' );
                    $query = $query->where ( 'disable', '=', "0" );
                    $query = $query->whereColumn ( 'trial_datetime', '!=', 'paid_subscription_start' )
                        ->where ( 'paid_subscription_start', '<=', $now )
                        ->where ( 'paid_subscription_end', '>=', $now );
                    $query = $query->join ( 'user_profiles', 'user_profiles.user_id', '=', 'users.id' );
                    break;
                case 'expired':
                    $now = date ( 'Y-m-d H:i:s' );
                    $query = $query->where ( 'paid_subscription_end', '<=', $now );
                    $query->join ( 'user_profiles', 'user_profiles.user_id', '=', 'users.id' );
                    $query = $query->where ( 'disable', '=', "0" );
                    break;
                case 'disabled':
                    $query = $query->where ( 'disable', '=', "1" );
                    $query->join ( 'user_profiles', 'user_profiles.user_id', '=', 'users.id' );
                    break;
                /*case 'other':
                    $now = date('Y-m-d H:i:s');
                    $query = $query->where('users.paid_subscription_start', '!=', $now)
                        ->where('users.paid_subscription_end', '<=', $now);
                    $query = $query->where('users.disable', '!=', "1");
                    $query->join('user_profiles', 'user_profiles.user_id', '=', 'users.id');
                    break;*/
                default :
                    break;
            }
        }

        $count = $query->count ();
        return $count;
    }


    public function keywordSearch($value)
    {
        $model = new UserProfile();
        $model = $model->where ( function ($subQuery) use ($value) {
            $subQuery
                //->where('email', 'like', '%' . $value . '%')
                ->orWhere ( 'first_name', 'like', '%' . $value . '%' )
                ->orWhere ( 'last_name', 'like', '%' . $value . '%' );
        } );
        return $model->get ();
    }

    /**
     * Update user roles.
     *
     * @param $userRoles
     * @param $userId
     * @return mixed
     */
    public function updateUserRolesByUserRoleIds($userRolesIds, $userId)
    {
        $user = User::findOrFail ( $userId );
        \Log::info ( $userRolesIds );
        \Log::info ( " ************** " );
        $user->roles ()->sync ( $userRolesIds );
    }


    public function updateUserPaidSubscription($type= null,$name = null)
    {
        $add_days = 0;
        $objUser = User::find ( Auth::user ()->id );
        if($type == "Monthly Billing"  || $type == "Monthly"){

            $add_days = 30;
            $objUser->billing_period = "monthly";
        }
        if($type == "Annual Billing"  || $type == "Annual"){

            $add_days = 365;
            $objUser->billing_period = "annual";
        }
        $objUser->subscription_level = $name;
        $objUser->paid_subscription_start =  date ( 'Y-m-d H:i:s' );
        $objUser->paid_subscription_end =  date ( 'Y-m-d', strtotime (  $add_days . 'days' ) );
        $objUser->updated_at =  date ( 'Y-m-d H:i:s' );
        $objUser->payment_status = 1;
        $objUser->subscription_renewal = "y";
        $objUser->save();
        return $objUser;

    }

}
