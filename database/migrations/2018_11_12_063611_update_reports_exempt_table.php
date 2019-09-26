<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateReportsExemptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasColumn ( 'reports', 'exempt' ) ) {
            Schema::table ( 'reports', function ($table) {
                $table->integer ( 'exempt' )->after ( 'available' )->default(1)->comment = "1=true, 0=false";
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


