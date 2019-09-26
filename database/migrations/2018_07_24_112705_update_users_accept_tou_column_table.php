<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersAcceptTouColumnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasColumn ( 'users', 'accept_tou' ) ) {
            Schema::table ( 'users', function ($table) {
                $table->String ( 'accept_tou' )->default('n')->comment = "type n=No, y=Yes";;
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
