<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class EntrustPermission extends \Zizaco\Entrust\Middleware\EntrustPermission
{

    public function handle($request, Closure $next, $permissions)
    {

        if (!is_array($permissions)) {
            $permissions = explode(self::DELIMITER, $permissions);
        }

        if ($this->auth->guest() || !$request->user()->can($permissions)) {
            if (($request->header('Content-Type') == 'application/json') ||
                ($request->header('Accept') == 'application/json')
            ){
                return response()->json(['Error' => __('messages.no_permission')], 403);
            } else{

                abort(403);
            }

        }

        return $next($request);
    }
}
