<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCompanyNewsInformaitonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_company_news_informaiton', function (Blueprint $table) {
            if (!Schema::hasColumn('user_company_news_informaiton', 'id')){
                $table->increments('id');
            }
            if (!Schema::hasColumn('user_company_news_informaiton', 'user_id')){
                $table->integer('user_id');
            }
            if (!Schema::hasColumn('user_company_news_informaiton', 'type')){
                $table->integer('type')->nullable()->default(1)->comment = "1 secondry, 2=tertiary";;
            }

            if (!Schema::hasColumn('user_company_news_informaiton', 'name')){
                $table->text('name');
            }
            if (!Schema::hasColumn('user_company_news_informaiton', 'name')){
                $table->text('name');
            }
            if (!Schema::hasColumn('user_company_news_informaiton', 'created_at')){
                $table->timestamps();
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_company_news_informaiton');
    }
}



