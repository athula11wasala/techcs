<?php

use Illuminate\Database\Seeder;
use App\Models\DataSet;

class Canibliation_Mysql_InTakeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataset = DataSet::where("data_set","5")->select("id")->first();
        $affected = DB::Connection("mysql_external_intake")->table('cannibalization')->update(array('dataset_id' => $dataset->id));

    }
}
