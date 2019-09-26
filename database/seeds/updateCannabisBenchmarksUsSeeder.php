<?php

use Illuminate\Database\Seeder;
use App\Models\CannabisBenchmarksUs;
use App\Models\DataSet;

class updateCannabisBenchmarksUsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $dataset = DataSet::where("data_set","4")->select("id")->first();
        $affected = DB::table('cannabis_benchmarks_us')->update(array('dataset_id' => $dataset->id));

    }
}
