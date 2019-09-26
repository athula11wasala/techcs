<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerifyTokenController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function verifyAuthUser(Request $request)
    {
        \Log::info("==== headers " , ['h' => $request->headers->all()]);

        \Log::info("==== verify auth token");
        $user = \Auth::user();

        if($user) {
            return response()->json(['authenticated' => true], 200);
        }
        return response()->json(['Error' => __('messages.authenticated_fail')], 400);
    }
}