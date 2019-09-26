<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZefyrConsumerGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       /* Schema::create('zefyr_consumer_group', function (Blueprint $table) {
            $table->increments('id');
            $table->date ( 'date' );
            $table->string ( 'state' )->default ( '' );
            $table->string ( 'consumer_group' )->default ( '' );
            $table->decimal ( 'male_pop' , 12, 2)->default ( 0 )->default ( 0 );
            $table->decimal ( 'female_pop', 12, 2 )->default ( '' )->default ( 0 );
            $table->decimal ( 'adult_dispensary_consumer', 12, 2 )->default ( 0 );
            $table->decimal ( 'adult_dispensary_total' , 12, 2)->default ( 0 );
            $table->decimal ( 'medical_dispensary_consumer', 12, 2 )->default ( 0 );
            $table->decimal ( 'medical_dispensary_total', 12, 2 )->default ( 0 );
            $table->decimal ( 'hybrid_dispensary_consumer', 12, 2 )->default ( 0 );
            $table->decimal ( 'hybrid_dispensary_total', 12, 2 )->default ( 0 );
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
        Schema::dropIfExists('zefyr_consumer_group');
    }
}
