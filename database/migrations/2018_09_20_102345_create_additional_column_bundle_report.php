<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdditionalColumnBundleReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasColumn ( 'bundle_report', 'bundle_woo_id' ) ) {

            Schema::table ( 'bundle_report', function ($table) {
                $table->String ( 'bundle_woo_id' )->default ( '' )->after ( 'mandatory' );
            } );

        }
        if ( !Schema::hasColumn ( 'bundle_report', 'bundled_item_id' ) ) {

            Schema::table ( 'bundle_report', function ($table) {
                $table->String ( 'bundled_item_id' )->default ( '' )->after ( 'bundle_woo_id' );

            } );

        }

        if ( !Schema::hasColumn ( 'bundle_report', 'bundle_product_id' ) ) {

            Schema::table ( 'bundle_report', function ($table) {
                $table->String ( 'bundle_product_id' )->default ( '' )->after ( 'bundled_item_id' );

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
