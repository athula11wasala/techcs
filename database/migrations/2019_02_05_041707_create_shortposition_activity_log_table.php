<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShortpositionActivityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shortposition_activity_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(null)->comment('equio user id');
            $table->string ( 'action' )->default ( '' );
            $table->text('log')->nullable();
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
        Schema::dropIfExists('shortposition_activity_log');
    }
}
