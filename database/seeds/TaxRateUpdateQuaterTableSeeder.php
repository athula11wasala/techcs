<?php

use Illuminate\Database\Seeder;
use App\Models\DataSet;

class TaxRateUpdateQuaterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $dataset = DataSet::where ( "data_set", "3" )->select ( "id" )->orderBy ( "id", "desc" )->first ();
        $afected = DB::Connection ( "mysql_external_intake" )->table ( 'taxrates' )->update ( array('dataset_id' => $dataset->id) );

        $result = DB::Connection ( "mysql_external_intake" )->table ( 'taxrates' )->select ( "*" )
            ->where ( "quarter", "!=", null )->get ();

        foreach ( $result as $rows ) {

            if ( $rows->quarter != null ) {

                $year = mb_substr ( $rows->quarter, 0, 4 );
                $quater = mb_substr ( $rows->quarter, 4, 6 );
                $quaterResult = $quater . " " . $year;
                if ( mb_substr ( $rows->quarter, 0, 1 ) != "Q" ) {
                    $affected = DB::Connection ( "mysql_external_intake" )->table ( 'taxrates' )->where ( "id", $rows->id )->update ( array('quarter' => $quaterResult) );
                }

            }

        }

    }
}
