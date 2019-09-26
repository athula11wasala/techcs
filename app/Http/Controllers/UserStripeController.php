<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserStripeRepository;
use App\Repositories\Criteria\Users\OrderByCreated;
use Join;

class UserStripeController extends Controller
{
    private $userStripeRepository;

    public function __construct(UserStripeRepository $userStripeRepository)
    {
        $this->userStripeRepository = $userStripeRepository;
    }

    public function addUserStripe(Request $request)
    {

        $userStripeData = $this->userStripeRepository->addUserStripe($request->all());
        if ($userStripeData) {
            return response()->json(['message' => __('user stripe created successfully')], 200);
        }

        return response()->json(['error' => __('messages.un_processable_request')], 400);

    }


    public function updateUserStripe(Request $request)
    {
        $userStripeData = $this->userStripeRepository->updateUserStripe($request->all());
        if ($userStripeData) {
            return response()->json(['message' => __('user stripe updated successfully')], 200);
        }

        return response()->json(['error' => __('messages.un_processable_request')], 400);

    }
}
