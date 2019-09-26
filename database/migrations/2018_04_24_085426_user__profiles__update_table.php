<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserProfilesUpdateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_profiles',function($table) {

            $table->integer('industry_role')->after('news_status')->nullable();
            $table->integer('news_company_header')->after('industry_role')->nullable();
            $table->integer('news_compny_detail')->after('news_company_header')->nullable();

        });

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
