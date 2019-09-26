<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCanbilzationMysqlintakeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'cannibalization', 'dataset_id' ) ) {
            Schema::connection ( 'mysql_external_intake' )->table ( 'cannibalization', function ($table) {
                $table->integer('dataset_id')->after('quarter')->default(1);
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



