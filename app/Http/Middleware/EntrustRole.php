<?php

namespace App\Http\Middleware;

use App\Repositories\SubscriptionRepository;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use App\Equio\Helper;

class EntrustRole extends \Zizaco\Entrust\Middleware\EntrustRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */

    private $routeName;
    private $subscriptions;

    public function __construct(SubscriptionRepository $subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    public function handle($request, Closure $next, $roles)
    {
        $helper = new Helper();
        $user = Auth::user ();
        $allRoleIds = [];
        $this->routeName = $request->getRequestUri ();
        $allUserAssignRole = $user->roles ()->get ();
        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }

        if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {
            \Log::info ( ' ********************* ' );
            //if ($user->reports_purchased == "n") {
            //user did not purchased any report check subscription activation
            $checkUser = $helper->checkTrailPeriodUser ( Auth::user ()->id, $this->routeName, $this->subscriptions );

            if ( $checkUser[ 'status' ] == false ) {
                $statusCode = 401;
                if ( $checkUser[ 'status_code' ] == Config::get ( "custom_config.ERROR.PERMISSION_DENIED" ) ) {
                    $statusCode = 403;
                }
                return response ( ["message" => $checkUser[ 'message' ], 'status' => $checkUser[ 'status_code' ]],
                    $statusCode );
            }

        }

        if ( !is_array ( $roles ) ) {
            $roles = explode ( self::DELIMITER, $roles );
        }

        if ( Auth::guest () || !$request->user ()->hasRole ( $roles ) ) {
            if ( ($request->header ( 'Content-Type' ) == 'application/json') ||
                ($request->header ( 'Accept' ) == 'application/json')
            ) {
                return response ()->json ( ['Error' => __ ( 'messages.no_permission' )], 403 );
            } else {
                abort ( 403 );
            }
        }

        return $next( $request );
    }


}