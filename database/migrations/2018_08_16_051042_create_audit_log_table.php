<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable ( 'audit_log' ) ) {
            Schema::create ( 'audit_log', function (Blueprint $table) {
                $table->increments ( 'id' );
                $table->integer ( 'user_id' );
                $table->string ( 'action' )->default ( '' );
                $table->integer ( 'object_id' );
                $table->string ( 'object_table' )->default ( '' );
                $table->string ( 'object_column' )->default ( '' );
                $table->string ( 'location' )->default ( '' );
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
        Schema::dropIfExists ( 'audit_log' );
    }
}

