<?php

use Illuminate\Database\Seeder;
use App\Models\DataSet;

class CaniblizationUpdateDataIdTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataset = DataSet::where("data_set","5")->select("id")->orderBy("id","desc")->first();
        $affected = DB::Connection("mysql_external_intake")->table('cannibalization')->where("quarter","Q2 2017")->update(array('dataset_id' => $dataset->id,'latest'=>1));

    }
}
