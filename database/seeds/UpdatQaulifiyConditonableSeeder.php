<?php

use Illuminate\Database\Seeder;
use App\Models\DataSet;

class UpdatQaulifiyConditonableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataset = DataSet::where("data_set","2")->select("id")->first();
        $affected = DB::table('qualifying_conditions')->update(array('dataset_id' => $dataset->id));
    }
}
