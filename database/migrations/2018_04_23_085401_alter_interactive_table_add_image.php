<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterInteractiveTableAddImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interactive_reports', function (Blueprint $table) {
            /*if (!Schema::hasColumn('interactive_reports', 'author_image'))
                $table->string('author_image')->nullable()->after('author');

            if (!Schema::hasColumn('interactive_reports', 'cover_image'))
                $table->string('cover_image')->nullable()->after('summary');*/
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
