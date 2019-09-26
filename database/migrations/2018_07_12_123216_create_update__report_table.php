<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpdateReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        if ( !Schema::hasColumn ( 'reports', 'publish_at' ) ) {
            Schema::table ( 'reports', function ($table) {
                $table->String ( 'publish_at' )->after ( 'updated_at' )->dafult('');
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
        Schema::dropIfExists('reports');
    }
}
