<?php

use Illuminate\Database\Seeder;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class SubscriptioinsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $subscription = new Subscription();
        $subscription->display_name = "Essential";
        $subscription->code = "essential";
        $subscription->status = 1;
        $subscription->save ();

        $subscription = new Subscription();
        $subscription->display_name = "Premium";
        $subscription->code = "premium";
        $subscription->status = 1;
        $subscription->save ();

        $subscription = new Subscription();
        $subscription->display_name = "Premium+";
        $subscription->code = "premium_plus";
        $subscription->status = 1;
        $subscription->save ();


        $subscription = new Subscription();
        $subscription->display_name = "Enterprise";
        $subscription->code = "enterprise";
        $subscription->status = 1;
        $subscription->save ();


    }
}