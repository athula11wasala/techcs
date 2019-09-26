<?php

use Illuminate\Database\Seeder;
use App\Models\Bundle;

class BundleUpdateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $affected = DB::table ( 'bundle' )->where ( "woo_id", 12928 )
            ->update ( array('cover_image' => 'InvestorExclusive.png'));

        $affected = DB::table ( 'bundle' )->where ( "woo_id", 13047 )
            ->update ( array('cover_image' => 'OperatorExclusive.png') );


        $affected = DB::table ( 'bundle' )->where ( "woo_id", 12937 )
            ->update ( array('cover_image' => 'ResearcherExclusive.png') );

        $affected = DB::table ( 'bundle' )->where ( "woo_id", 12914 )
            ->update ( array('cover_image' => 'BuildYourOwnBundle.png') );


    }
}




