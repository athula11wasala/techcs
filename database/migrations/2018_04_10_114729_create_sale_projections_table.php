<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleProjectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::connection('mysql_external_intake')->create('sale_projections', function($table)
        {
            $table->increments('id');
            $table->integer('year');
            $table->double('medical');
            $table->double('recreational');
            $table->double('total');
            $table->tinyInteger('latest')->nullable()->default(0);
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
        Schema::dropIfExists('sale_projections');
    }


}


