<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserShortTrackersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_short_trackers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(null)->comment('equio user id');
            $table->string('symbol', 100)->default('')->comment('company symbol');
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
        Schema::dropIfExists('user_short_trackers');
    }
}
