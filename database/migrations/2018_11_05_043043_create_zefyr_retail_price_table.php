<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZefyrRetailPriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      /*  Schema::create('zefyr_retail_price', function (Blueprint $table) {
            $table->increments('id');
            $table->string ( 'market' )->default ( '' );
            $table->string ( 'state' )->default ( '' );
            $table->string ( 'product_category' )->default ( '' );
            $table->string ( 'sub_type' )->default ( '' );  //strain
            $table->string ( 'quntity_type' )->default ( '' );
            $table->float ( 'avg_price' )->default ( 0 );
            $table->float ( 'min_price' )->default ( 0 );
            $table->float ( 'max_price' )->default ( 0 );
            $table->date ( 'date' );
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
      */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zefyr_retail_price');
    }
}
