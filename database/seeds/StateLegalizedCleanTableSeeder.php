<?php

use Illuminate\Database\Seeder;
use App\Models\DataSet;

class StateLegalizedCleanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataset = DataSet::where("data_set","1")->select("id")->first();
        $affected = DB::Connection("mysql_external_intake")->table('state_legalized_clean')->update(array('dataset_id' => $dataset->id));
    }
}
