<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CompanyProfiles extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('company_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('country');
            $table->tinyInteger('profile_type')->nullable()->default(1);
            $table->string('profile_cover', 255);
            $table->string('profile_document', 255);
            $table->integer('profile_order')->nullable()->default(0);
            $table->integer('created_by');
            $table->integer('updated_by')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_profiles');
    }

}
