<?php

use Illuminate\Database\Seeder;
use App\Models\DataSet;

class TaxRateAddDataSetTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataset = DataSet::where("data_set","3")->select("id")->orderBy("id", "desc")->first();
        $afected = DB::Connection ( "mysql_external_intake" )->table ( 'taxrates' )->update(array('dataset_id' => $dataset->id));
    }
}
