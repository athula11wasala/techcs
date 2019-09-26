<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModuleSubscriptionTrackersTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('module_subscription_trackers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(null)->comment('equio user id');
            $table->string('subscription_id', 100)->default('')->comment('user subscription id on stripe');
            $table->string('plan_id', 100)->default('')->comment('stripe plan id');
            $table->string('payment_gateway', 100)->default('')->comment('stripe paypal or ect...');
            $table->integer('subscription_status')->default(0)->comment('0 = pending, 1 = active, 2 = cancel, 3 = pending cancel');
            $table->string('subscription_name', 250)->default('')->comment('name of the related module');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('module_subscription_trackers');
    }
}
