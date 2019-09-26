<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveStripeDetailsFromUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            if (Schema::hasColumn('users', 'card_brand')){
                $table->dropColumn('card_brand');
            }
            if (Schema::hasColumn('users', 'card_last_four')){
                $table->dropColumn('card_last_four');
            }
            if (Schema::hasColumn('users', 'trial_ends_at')){
                $table->dropColumn('trial_ends_at');
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
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
