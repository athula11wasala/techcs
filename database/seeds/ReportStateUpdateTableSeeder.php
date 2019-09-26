<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportStateUpdateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $affectedOne = DB::table ( 'reports' )->where ( "state", 'DC' )
            ->update ( array('state' => 'District of Columbia') );

        $affectedTwo = DB::table ( 'reports' )->where ( "state", 'New-Hamphire' )
            ->update ( array('state' => 'New Hampshire') );

        $affectedThree = DB::table ( 'reports' )->where ( "state", 'New-Jersey' )
            ->update ( array('state' => 'New Jersy') );

        $affectedFour = DB::table ( 'reports' )->where ( "state", 'New-Mexico' )
            ->update ( array('state' => 'New Mexico') );

        $affectedFive = DB::table ( 'reports' )->where ( "state", 'New-York' )
            ->update ( array('state' => 'New York') );

        $affectedSix = DB::table ( 'reports' )->where ( "state", 'Massachussetts' )
            ->update ( array('state' => 'Massachusetts') );

        $affectedSix = DB::table ( 'reports' )->where ( "state", 'North-Dakota' )
            ->update ( array('state' => 'North Dakota') );

        $affectedSix = DB::table ( 'reports' )->where ( "state", 'calfornia' )
            ->update ( array('state' => 'California') );
    }
}






