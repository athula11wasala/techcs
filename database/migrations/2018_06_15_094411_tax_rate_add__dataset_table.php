<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaxRateAddDatasetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /*if ( !Schema::connection ( 'mysql_external_intake' )->hasColumn ( 'taxrates', 'dataset_id' ) ) {

            Schema::connection ( 'mysql_external_intake' )->table ( 'taxrates', function ($table) {
                $table->integer ( 'dataset_id' )->after ( 'quarter' )->default ( 0 );
            } );


        }*/

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



