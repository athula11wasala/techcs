<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvestmentRankingStateUsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::connection ( 'mysql_external_intake' )->hasTable ( 'investment_ranking_state_us' ) ) {

            Schema::connection ( 'mysql_external_intake' )->create ( 'investment_ranking_state_us', function (Blueprint $table) {

                $table->increments ( 'id' );
                $table->string ( 'state' );
                $table->string ( 'legalization' )->default ( '' );;
                $table->string ( 'cultivation' )->default ( '' );
                $table->string ( 'retail' )->default ( '' );
                $table->string ( 'manufacturing' )->default ( '' );
                $table->string ( 'distribution'  )->default ( '' );
                $table->string ( 'ancillary' )->default ( '' );
                $table->string ( 'risk' )->default ( '');
                $table->string ( 'opportunity' )->default ( '' );
                $table->string ( 'description' )->default ( '' );
                $table->integer ( 'dataset_id' )->default ( 0 );
                $table->integer ( 'latest' )->default ( 0 );
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
        Schema::connection ( 'mysql_external_intake' )->dropIfExists ( 'investment_ranking_state_us' );
    }
}



