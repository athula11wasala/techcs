<?php

namespace App\Http\Middleware;

use App\Repositories\SubscriptionRepository;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\RoleUser;
use Illuminate\Support\Facades\Config;
use App\Equio\Helper;

class ReportChart
{

    private $routeName;
    private $subscriptions;

    public function __construct(SubscriptionRepository $subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user ();
        $helper = new Helper();
        $this->routeName = $request->getRequestUri ();
        $status = $helper->checkTrailPeriodUser ( Auth::user ()->id, $this->routeName, $this->subscriptions );
        $status_code = '';
        if ( !empty( $status[ 'status_code' ] ) ) {
            $status_code = $status[ 'status_code' ];

        }
        if ( $status[ 'status' ] == false ) {

            return response ()->json ( ['Error' => __ ( 'messages.no_permission' ), 'status_code' => $status_code], 403 );
        }
        return $next( $request );
    }

}
