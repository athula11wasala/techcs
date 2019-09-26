<?php

namespace App\Http\Middleware;

use Closure;

class EntrustAbility extends \Zizaco\Entrust\Middleware\EntrustAbility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $roles, $permissions, $validateAll = false)
    {
        if (!is_array($roles)) {
            $roles = explode(self::DELIMITER, $roles);
        }

        if (!is_array($permissions)) {
            $permissions = explode(self::DELIMITER, $permissions);
        }

        if (!is_bool($validateAll)) {
            $validateAll = filter_var($validateAll, FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->auth->guest() || !$request->user()->ability($roles,
                $permissions, [ 'validate_all' => $validateAll ])) {
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
