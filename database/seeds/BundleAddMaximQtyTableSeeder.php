<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BundleAddMaximQtyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $affectedOne = DB::table ( 'bundle' )->where ( "name", 'Investor Exclusive Offer: Investor Bundle' )
            ->update ( array('min_qty' =>7,'max_qty' =>7) );

        $affectedOne = DB::table ( 'bundle' )->where ( "name", 'Operator Exclusive Offer: Operator Bundle' )
            ->update ( array('min_qty' =>2,'max_qty' =>2 ) );

        $affectedOne = DB::table ( 'bundle' )->where ( "name", 'Researcher Exclusive Offer: Researcher Bundle' )
            ->update ( array('min_qty' =>2,'max_qty' =>17 ) );

        $affectedOne = DB::table ( 'bundle' )->where ( "name", 'Build Your Own Bundle' )
            ->update ( array('min_qty' =>3,'max_qty' =>3 ) );

    }
}



