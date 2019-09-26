<?php

use Illuminate\Database\Seeder;
use App\Models\DataSet;

class StateLegalizedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataset = DataSet::where("data_set","1")->select("id")->first();
        $affected = DB::table('state_legalized')->update(array('dataset_id' => $dataset->id));
    }
}
