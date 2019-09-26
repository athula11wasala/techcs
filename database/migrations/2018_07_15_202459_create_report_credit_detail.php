<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportCreditDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable ( 'credit_user_detail' ) ) {
            Schema::create ( 'credit_user_detail', function (Blueprint $table) {
                $table->increments ( 'id' );
                $table->integer ( 'report_id' );
                $table->integer ( 'credit_user_id' );
                $table->tinyInteger ( 'type' )->nullable ()->default ( 1 )->comment = "type 1=author, 2=research 3=editor 3=editor 4 = markeing&PR";
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

    }
}
