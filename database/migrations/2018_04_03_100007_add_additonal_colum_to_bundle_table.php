<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditonalColumToBundleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::table('bundle', function (Blueprint $table) {

            $table->integer('woo_id')->after('id');
            $table->string('price')->after('name');
            $table->string('cover_image')->after('name');
            $table->string('purchase_url_link')->after('name');

        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bundle');
    }
}
