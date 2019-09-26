<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportHardCopyDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable ( 'report_Hard_copy_detail' ) ) {
            Schema::create ( 'report_Hard_copy_detail', function (Blueprint $table) {
                $table->increments ( 'id' );
                $table->integer ( 'report_id' );
                $table->string ( 'woo_id' );
                $table->float ( 'price' );
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
        //
    }
}
