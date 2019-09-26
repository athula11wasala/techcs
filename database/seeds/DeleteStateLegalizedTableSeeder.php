<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteStateLegalizedTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $deleteTable = DB::connection ( 'mysql_external_intake' )->select ( DB::raw ( "
               DROP TABLE IF EXISTS state_legalized

        " ) );

        $deleteTable = DB::connection ( 'mysql_external_intake' )->select ( DB::raw ( "
             
                 ALTER TABLE state_legalized_clean RENAME state_legalized  

        " ) );


    }
}




