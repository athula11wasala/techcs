<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateQualifyConditionsAddTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'ptsd' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'ptsd' )->after ( 'neurofibromatosis' );
            } );
        }

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'amyotrophic_sclerosis' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'amyotrophic_sclerosis' )->after ( 'ptsd' );
            } );
        }

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'parkinson' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'parkinson' )->after ( 'amyotrophic_sclerosis' );
            } );
        }

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'terminal' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'terminal' )->after ( 'parkinson' );
            } );
        }

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'bipolar' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'bipolar' )->after ( 'terminal' );
            } );
        }


        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'chronic_fatigue' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'chronic_fatigue' )->after ( 'bipolar' );
            } );
        }

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'diabetes' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'diabetes' )->after ( 'chronic_fatigue' );
            } );
        }

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'endometriosis_PMS' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'endometriosis_PMS' )->after ( 'diabetes' );
            } );
        }


        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'insomnia' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'insomnia' )->after ( 'endometriosis_PMS' );
            } );
        }

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'lyme' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'lyme' )->after ( 'insomnia' );
            } );
        }

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'ocd' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'ocd' )->after ( 'lyme' );
            } );
        }

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'rheumatoid' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'rheumatoid' )->after ( 'ocd' );
            } );
        }

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'sickle_anemia' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'sickle_anemia' )->after ( 'rheumatoid' );
            } );
        }

        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'qualifying_conditions', 'colitis' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'qualifying_conditions', function ($table) {
                $table->string ( 'colitis' )->after ( 'sickle_anemia' );
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




