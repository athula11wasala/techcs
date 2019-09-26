<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBundleReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::create('bundle_report', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('report_id');
            $table->tinyInteger('bundle_id');
            $table->timestamps();
        });*/


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bundle_report');
    }
}
