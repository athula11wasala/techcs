<?php

namespace App\Http\Controllers\Auth;

use App\Models\Account;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\RoleUser;
use App\Models\User;
use App\Services\PaymentService;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Http\Controllers\AccessTokenController as ATC;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use Response;
use App\Equio\Helper;
use App\Repositories\SubscriptionRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class AccessTokenController extends ATC {

    private $routeName;
    private $subscriptions;

    public function issueToken(ServerRequestInterface $request) {

        try {
            //get username (default is :email)
            $username = $request->getParsedBody ()[ 'username' ];

            $password = $request->getParsedBody ()[ 'password' ];

            \Log::info ( "==== " . __FUNCTION__ . " request ip : " . request ()->ip () );

            //get user
            $user = User::where ( 'email', '=', $username )->select ( ['users.id', 'users.encrypted_password', 'users.email', 'users.password',
                'users.current_sign_in_at', 'users.current_sign_in_ip', 'users.sign_in_count', "users.role",
                'user_profiles.first_name', 'user_profiles.last_name', 'users.subscription_level'
                , 'user_profiles.country as country_id', 'user_profiles.state as state_id', 'users.disable', 'reports_purchased', 'users.paid_subscription_start', 'users.paid_subscription_end'] )
                ->join ( 'user_profiles', 'user_profiles.user_id', '=', 'users.id' )->first ();

            if ( !$user ) {
                \Log::info ( "==== " . __FUNCTION__ . " Account not found : " );
                return response ( ["message" => "The user credentials were incorrect"], 401 );
            }

            if ( $user ) {

                if ( $user->password == $user->encrypted_password ) {
                    $checkMd5User = User::where ( 'email', '=', $username )
                        ->where ( 'password', '=', md5 ( $password ) )
                        ->select ( ['users.id', 'users.email'] )
                        ->first ();

                    if ( !empty( $checkMd5User ) ) {
                        if ( $this->isValidMd5 ( $user->password ) ) {
                            $user->password = bcrypt ( $password );
                            $user->save ();
                        } else {
                            return response ( ["message" => "The user credentials were incorrect"], 401 );
                        }

                    } else {
                        return response ( ["message" => "The user credentials were incorrect"], 401 );
                    }
                }
            }

            //if user status is disable use cannot login to system.
            if ( $user->disable == 1 ) {
                return response ( ["message" => "The user is deactivated"], 401 );
            }

            $user = User::find ( $user->id );
            $objHelper = new Helper();
            $allRoleIds = [];
            $allUserAssignRole = $user->roles ()->get ();
            foreach ( $allUserAssignRole as $rows ) {
                array_push ( $allRoleIds, $rows->id );
            }

            if ( $objHelper->checkAuthnicatedRole ( $allRoleIds ) == false ) {

                if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {
                    $subscription = $objHelper->userSubscription ( $user->paid_subscription_start, $user->paid_subscription_end );
                    if ( !$subscription ) {
                        if ( $user->reports_purchased == "n" ) {
                           // return response ( ["message" => "Your subscription has expired.", "expiredStatus" => 1], 401 );
                        } else if ( $user->subscription_level != 'essential' && $user->reports_purchased == "y" ) {
                            // return response(["message" => "Your subscription has expired."], 401);
                        }

                    }
                }
            }


            $this->updateCurrentSignInStatus ( $user );

            //generate token
            $tokenResponse = parent::issueToken ( $request );

            //convert response to json string
            $content = $tokenResponse->getContent ();

            //convert json to array
            $data = json_decode ( $content, true );

            if ( isset( $data[ "error" ] ) ) {
                \Log::error ( "==== auth token got errors  ", ['e' => $data[ "error" ]] );
                throw new OAuthServerException( 'The user credentials were incorrect.', 6, 'invalid_credentials', 401 );
            }

            $user = $this->transformUserData ( $user, $data );

            return Response::json ( $user );
        } catch (ModelNotFoundException $e) { // email notfound
            //return error message
            return response ( ["message" => "Account is not found"], 406 );
        } catch
        (OAuthServerException $e) { //password not correct..token not granted
            //return error message
            return response ( ["message" => $e->getMessage ()], 406 );
        } catch (Exception $e) {
            ////return error message
            return response ( ["message" => "Internal server error " . $e->getMessage ()], 500 );
        }
    }


    public function userRolesAndPermissions($user_id) {

        $all = RoleUser::where ( 'user_id', $user_id )
            ->with ( ['role', 'permissionRole.permission'] )
            ->with ( array('role' => function ($query) {
                $query->select ( 'id', 'name', 'display_name', 'description'
                );
            },
                'permissionRole.permission' => function ($query)  {
                    $query->select ( 'id', 'name', 'display_name', 'description'
                    );
                }
            ) )
            ->get ();
        return $all;
    }

    public function getAllPermission($userId,$removePermission= [] ) {
        $user = User::find ( $userId );
        $objHelper = new Helper();
        $allRoleIds = [];
        $allUserAssignRole = $user->roles ()->get ();
        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }

        $userPermisson = [];
        $permissionIds = PermissionRole::select ( 'permission_id' )
            ->join ( 'role_user', 'permission_role.role_id', 'role_user.role_id' )
            ->where ( 'role_user.user_id', '=', $userId )
            ->whereNotIn("permission_role.permission_id",$removePermission)
            ->get ();

        $allPermission = Permission::select ( "name" )->whereIn ( "id", $permissionIds )->get ();
        foreach ( $allPermission as $permissionVal ) {
            $userPermisson[] = $permissionVal->name;
        }

        $user = User::where ( 'id', '=', $userId )->select ( ['users.id', 'users.reports_purchased', 'users.paid_subscription_start', 'users.paid_subscription_end'] )->first ();
        // check whether user expired or not
        if ( $objHelper->checkAuthnicatedRole ( $allRoleIds ) == false ) {

            if ( !$objHelper->userSubscription ( $user->paid_subscription_start, $user->paid_subscription_end ) && in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {

                $userPermisson[] = 'EXPIRED';
                $userPermisson[] = 'EQUIO_EXPIRED';
            }
        }


        return $userPermisson;
    }

    public function searchAccess($user) {
        $helper = new Helper();
        $this->routeName = '/api/search/authenticate'; // $request->getRequestUri();
        $status = $helper->checkTrailPeriodUserSearch ( $user[ 'id' ], $this->routeName, '' );
        $serach_access = false;
        if ( !empty( $status ) ) {
            if ( $status[ 'status' ] == 1 ) {
                $serach_access = true;
            } else {
                $serach_access = false;
            }

        }
        return $serach_access;
    }


    private function transformUserData($user, $data) {
        $country = empty( $user->country_id ) ? $user->country_id : '';
        $state = empty( $user->state_id ) ? $user->state_id : '';
        $subscritpion_level = !empty( $user->subscription_level ) ? $user->subscription_level : '';
        $remove_permission = [];
        /* getting user has active subcrition list from stripe */
        $user_stripe_subcription = false;
        $objPaymentService = new PaymentService();
        $obj_active_subcription_info = $objPaymentService->getUserActiveSubscriptionId ( $user->email );

        if ( isset( $obj_active_subcription_info[ 'code' ] ) ) {

            if ( ($obj_active_subcription_info[ 'code' ] == '400') ) {
                $user_stripe_subcription = false;
            }
            if ( ($obj_active_subcription_info[ 'code' ] == '200') ) {

                if ( isset( $obj_active_subcription_info[ 'subscriptionId' ] ) ) {

                    if ( !empty( $obj_active_subcription_info[ 'subscriptionId' ] ) ) {

                        $user_stripe_subcription = true;

                    } else {

                        $user_stripe_subcription = false;
                    }
                }
            }
        }

        $user_access_expired = false;
        $reports_purchased = $user->reports_purchased;
        //*
        $objHelper = new Helper();
        $role_num = $objHelper->getUserAssingRoleNum ( $user->id );
        if ( !empty( $role_num ) ) {
            $chkUserRoleAccess = $objHelper->checkAuthnicatedRole ( $role_num );

            if ( empty( $chkUserRoleAccess ) ) {

                $chk_expired = $objHelper->userSubscription ( $user->paid_subscription_start, $user->paid_subscription_end );
                if ( empty( $chk_expired ) ) {

                    $user_access_expired = true;
                }
            }
        }

        if($user_access_expired == true  &&  $user_stripe_subcription == true   &&  $reports_purchased== "y"  ){


            $report_permission = DB::table("permissions")->where("name","REPORTS_CHARTS")->select("id")->first();
            $remove_permission[] = $report_permission->id;
        }

        if($user_access_expired == true  &&  $user_stripe_subcription == false   &&  $reports_purchased== "n"  ){

            $report_permission = DB::table("permissions")->where("name","REPORTS_CHARTS")->select("id")->first();
            $remove_permission[] = $report_permission->id;
        }
        $permissions = $this->userRolesAndPermissions ( $user->id);
        $userPermisson = $this->getAllPermission ( $user->id,$remove_permission );

        if ( $user->sign_in_count < 4 ) {

            $checkUserInterst = DB::table ( "user_interest" )->where ( "user_id", $user->id )->first ();

            if ( empty( $checkUserInterst ) ) {
                $investmentInterest = true;

            } else {
                $investmentInterest = false;
            }

        } else {
            $investmentInterest = false;
        }
        $first_login = ($user[ 'sign_in_count' ] == 1) ? true : false;
        $user = collect ( $user );
        $serach_access = $this->searchAccess ( $user );

        $user->pull ( 'last_sign_in_at' );
        $user->pull ( 'sign_in_count' );
        $user->pull ( 'last_sign_in_ip' );
        $user->pull ( 'updated_at' );
        $user->pull ( 'current_sign_in_at' );
        $user->pull ( 'country', $country );
        $user->pull ( 'state', $state );
        $user->put ( 'access_token', $data[ 'access_token' ] );
        $user->put ( 'token_type', $data[ 'token_type' ] );
        $user->put ( 'expires_in', $data[ 'expires_in' ] );
        $user->put ( 'refresh_token', $data[ 'refresh_token' ] );
        $user->put ( 'refresh_token', $data[ 'refresh_token' ] );
        $user->put ( 'subscription_level', $subscritpion_level );
        $user->put ( 'investment_interest', $investmentInterest );
        $user->put ( 'permission', $userPermisson );
        $user->put ( 'user_permission', $permissions );
        $user->put ( 'first_login', $first_login );
        $user->put ( 'is_search_access', $serach_access );
        $user->put ( 'is_user_access_expired', $user_access_expired );
        $user->put ( 'is_reports_purchased', $reports_purchased );
        $user->put ( 'is_user_stripe_subcription', $user_stripe_subcription );

        return $user;
    }

    private function isValidMd5($md5 = '') {
        return strlen ( $md5 ) == 32 && ctype_xdigit ( $md5 );
    }

    /**
     * @param $user
     */
    public function updateCurrentSignInStatus($user) {
        if ( $user->current_sign_in_at != '' ) {
            $last_sign_in = $user->current_sign_in_at;
            $user->last_sign_in_at = $last_sign_in;
        }

        if ( $user->current_sign_in_ip != '' ) {
            $last_sign_in_ip = $user->current_sign_in_ip;
            $user->last_sign_in_ip = $last_sign_in_ip;
        }

        $user->current_sign_in_at = new Carbon();
        $user->last_sign_in_at = $user->current_sign_in_at;
        $user->current_sign_in_ip = request ()->ip ();
        $user->sign_in_count = ($user->sign_in_count + 1);

        $user->save ();
    }

}