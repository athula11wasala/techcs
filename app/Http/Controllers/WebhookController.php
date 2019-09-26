<?php

namespace App\Http\Controllers;

use App\Events\SubscriptionPurchased;
use Illuminate\Http\Request;

class WebhookController extends Controller
{

    public function __construct(Request $request)
    {
        $this->processWebhookData($request);
    }

    private function processWebhookData($data)
    {

    }

}
