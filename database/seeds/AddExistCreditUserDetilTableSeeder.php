<?php

use Illuminate\Database\Seeder;
use App\Models\InteractiveReport;
use App\Models\CreditUser;
use Illuminate\Support\Facades\DB;


class AddExistCreditUserDetilTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $objInteractive = DB::table ( "interactive_reports" )->select ( "*" )->get ();

        foreach ( $objInteractive as $rows ) {

            if ( !empty( $rows->author ) ) {

                $this->addAuthor ( $rows->author, $rows->report_id );

            }


            if ( !empty( $rows->analysts ) ) {

                $this->addresearch ( $rows->author, $rows->report_id );

            }


            if ( !empty( $rows->editor ) ) {

                $this->addEditor ( $rows->author, $rows->report_id );

            }


            if ( !empty( $rows->marketing ) ) {

                $this->addmarkeing ( $rows->author, $rows->report_id );

            }

        }


    }


    public function addAuthor($author = null, $reportId = null)
    {


        $objCreditUser = DB::table ( 'credit__user' )->where ( "type", 1 )->where ( "description", $author )->first ();

        $arrAuthor = explode ( ",", $author );
        if ( isset( $arrAuthor[ 0 ] ) ) {

            $objCreditUser = DB::table ( 'credit__user' )->where ( "type", 1 )->where ( "name", $arrAuthor[ 0 ] )->first ();
            if ( isset( $objCreditUser->id ) ) {


             $existRecords =  DB::table("credit_user_detail")->select("id")->where("report_id",$reportId)->where("credit_user_id",$objCreditUser->id)
                    ->where("type",1)->first();

             if(empty($existRecords)){

                 DB::table ( "credit_user_detail" )->insert ( ['report_id' => $reportId, 'credit_user_id' => $objCreditUser->id, 'type' => 1] );
             }



            }
        }

    }

    public function addresearch($author = null, $reportId = null)
    {


        $objCreditUser = DB::table ( 'credit__user' )->where ( "type", 2 )->where ( "description", $author )->first ();

        $arrAuthor = explode ( ",", $author );
        if ( isset( $arrAuthor[ 0 ] ) ) {

            $objCreditUser = DB::table ( 'credit__user' )->where ( "type", 2 )->where ( "name", $arrAuthor[ 0 ] )->first ();
            if ( isset( $objCreditUser->id ) ) {

                $existRecords =  DB::table("credit_user_detail")->select("id")->where("report_id",$reportId)->where("credit_user_id",$objCreditUser->id)
                    ->where("type",2)->first();

                if(empty($existRecords)) {
                    DB::table ( "credit_user_detail" )->insert ( ['report_id' => $reportId, 'credit_user_id' => $objCreditUser->id, 'type' => 2] );
                }
            }
        }

    }


    public function addEditor($author = null, $reportId = null)
    {


        $objCreditUser = DB::table ( 'credit__user' )->where ( "type", 3 )->where ( "description", $author )->first ();

        $arrAuthor = explode ( ",", $author );
        if ( isset( $arrAuthor[ 0 ] ) ) {

            $objCreditUser = DB::table ( 'credit__user' )->where ( "type", 3 )->where ( "name", $arrAuthor[ 0 ] )->first ();
            if ( isset( $objCreditUser->id ) ) {

                $existRecords =  DB::table("credit_user_detail")->select("id")->where("report_id",$reportId)->where("credit_user_id",$objCreditUser->id)
                    ->where("type",3)->first();
                if(empty($existRecords)) {
                    DB::table ( "credit_user_detail" )->insert ( ['report_id' => $reportId, 'credit_user_id' => $objCreditUser->id, 'type' => 3] );
                }
            }
        }

    }

    public function addmarkeing($author = null, $reportId = null)
    {

        $objCreditUser = DB::table ( 'credit__user' )->where ( "type", 4 )->where ( "description", $author )->first ();

        $arrAuthor = explode ( ",", $author );
        if ( isset( $arrAuthor[ 0 ] ) ) {

            $objCreditUser = DB::table ( 'credit__user' )->where ( "type", 4 )->where ( "name", $arrAuthor[ 0 ] )->first ();
            if ( isset( $objCreditUser->id ) ) {

                $existRecords =  DB::table("credit_user_detail")->select("id")->where("report_id",$reportId)->where("credit_user_id",$objCreditUser->id)
                    ->where("type",4)->first();
                if(empty($existRecords)) {
                    DB::table ( "credit_user_detail" )->insert ( ['report_id' => $reportId, 'credit_user_id' => $objCreditUser->id, 'type' => 4] );
                }
            }
        }

    }


}


