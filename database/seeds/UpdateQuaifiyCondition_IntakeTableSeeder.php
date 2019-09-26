<?php

use Illuminate\Database\Seeder;
use App\Models\DataSet;


class UpdateQuaifiyCondition_IntakeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataset = DataSet::where("data_set","2")->select("id")->first();
        $affected = DB::Connection("mysql_external_intake")->table('qualifying_conditions')->update(array('dataset_id' => $dataset->id));
    }
}
