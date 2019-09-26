<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateQualifyconstionIntakeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        if ( Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'dataset_id' ) ) {

        } else {

            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->integer ( 'dataset_id' )->default ( 0 );

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





