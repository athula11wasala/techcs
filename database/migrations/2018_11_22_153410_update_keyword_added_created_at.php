<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateKeywordAddedCreatedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( Schema::hasTable ( 'keywords' ) ) {

            if ( !Schema::hasColumn ( 'keywords', 'created_at' )  && !Schema::hasColumn ( 'keywords', 'updated_at' ) ) {

                Schema::table('keywords', function ($table) {

                    $table->timestamps();

                });

            }
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
