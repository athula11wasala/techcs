<?php

namespace App\Handlers\Events;

use App\Events\SubscriptionPurchased;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PurchaseConfirmation
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SubscriptionPurchased  $event
     * @return void
     */
    public function handle(SubscriptionPurchased $event)
    {
        //
    }
}
