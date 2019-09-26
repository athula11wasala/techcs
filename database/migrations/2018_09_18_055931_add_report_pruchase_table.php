<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReportPruchaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_purchase', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stripe_details_id');
            $table->integer('user_id');
            $table->string('email');
            $table->integer('product_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_purchase', function (Blueprint $table) {
            //
        });
    }
}


