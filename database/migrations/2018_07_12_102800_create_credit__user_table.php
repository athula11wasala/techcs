<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable ( 'credit__user' ) ) {
        Schema::create ( 'credit__user', function (Blueprint $table) {
            $table->increments ( 'id' );
            $table->string ( 'name' );
            $table->string ( 'description' );
            $table->tinyInteger ( 'type' )->nullable ()->default ( 1 )->comment = "type 1=author, 2=research 3=editor 3=editor 4 = markeing&PR";
            $table->timestamps ();
        } );
    }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit__user');
    }
}
