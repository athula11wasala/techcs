<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLatestIdQualifyConditiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'latest' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->integer ( 'latest' )->after ( 'colitis' )->default ( 0 );
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

    }
}
