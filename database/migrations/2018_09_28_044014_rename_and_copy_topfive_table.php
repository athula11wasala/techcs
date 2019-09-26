<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAndCopyTopfiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $result = DB::statement(
            DB::raw("CREATE TABLE insight_daily_us  (
                      id int(11)  UNSIGNED AUTO_INCREMENT PRIMARY KEY  not null,
                      date varchar(45) DEFAULT NULL,
                      source varchar(45) DEFAULT NULL,
                      headline varchar(256) DEFAULT NULL,
                      full_story varchar(512) DEFAULT NULL,
                      image_url varchar(2048) DEFAULT NULL,
                      topic varchar(32) DEFAULT NULL,
                      source_url varchar(2048) DEFAULT NULL
                    ) SELECT * FROM cms.top5")  );

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




