<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopFivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_fives', function (Blueprint $table) {
            $table->increments('id');
            $table->char('date',45);
            $table->char('source',45);
            $table->string('headline',256);
            $table->string('full_story',512);
            $table->string('image_url',2048);
            $table->char('topic',32);
            $table->string('source_url',2048);
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
        Schema::dropIfExists('top_fives');
    }
}
