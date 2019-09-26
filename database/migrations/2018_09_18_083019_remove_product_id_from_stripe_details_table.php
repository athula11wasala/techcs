<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveProductIdFromStripeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stripe_details', function (Blueprint $table) {
            //
            if (Schema::hasColumn('stripe_details', 'product_id')){
                $table->dropColumn('product_id');
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stripe_details', function (Blueprint $table) {
            //
        });
    }
}
