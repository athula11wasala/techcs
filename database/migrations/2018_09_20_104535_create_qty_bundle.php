<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQtyBundle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasColumn ( 'bundle', 'min_qty' ) ) {

            Schema::table ( 'bundle', function ($table) {
                $table->Integer ( 'min_qty' )->default ( 0 )->after ( 'status' );
            } );

        }
        if ( !Schema::hasColumn ( 'bundle', 'max_qty' ) ) {

            Schema::table ( 'bundle', function ($table) {
                $table->Integer ( 'max_qty' )->default ( 0)->after ( 'status' );
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
        //
    }
}
