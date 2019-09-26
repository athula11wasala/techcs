<?php

use Illuminate\Database\Seeder;
use App\Models\Cannibalization;
use App\Models\DataSet;

class updateCannibalizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataset = DataSet::where("data_set","5")->select("id")->orderBy('id', 'desc')->first();
        $affected = DB::table('cannibalization')->update(array('dataset_id' => $dataset->id));

    }
}
