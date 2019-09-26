<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZefyrConsumerTapestryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       /* Schema::create('zefyr_consumer_tapestry', function (Blueprint $table) {
            $table->increments('id');
            $table->string ( 'consumer_group' )->default ( '' );
            $table->decimal ( 'median_income' , 12, 2)->default ( 0 )->default ( 0 );
            $table->text ( 'top_professions' )->default ( '' );
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
        Schema::dropIfExists('zefyr_consumer_tapestry');
    }
}

