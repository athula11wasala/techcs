<?php

use Illuminate\Database\Seeder;
use App\Models\DataSet;
use Carbon\Carbon;


class UpdateDataSetsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $objDataset =  DataSet::find(1);
        $objDataset->from =  Carbon::parse('2018-03-31');
        $objDataset->to = Carbon::parse('2018-06-31');
        $objDataset->save();

        $objDataset =  DataSet::find(2);
        $objDataset->from =  Carbon::parse('2018-07-01');
        $objDataset->to = Carbon::parse('2018-10-30');
        $objDataset->save();


        $objDataset = DataSet::find(3);
        $objDataset->from =  Carbon::parse('2018-04-01');
        $objDataset->to = Carbon::parse('2018-08-30');
        $objDataset->save();

        $objDataset = DataSet::find(4);
        $objDataset->from =  Carbon::parse('2018-03-01');
        $objDataset->to = Carbon::parse('2018-08-30');
        $objDataset->save();

        $objDataset = DataSet::find(5);
        $objDataset->from =  Carbon::parse('2018-03-31');
        $objDataset->to = Carbon::parse('2018-06-31');
        $objDataset->save();

        $objDataset = DataSet::find(6);
        $objDataset->from =  Carbon::parse('2018-03-31');
        $objDataset->to = Carbon::parse('2018-06-31');
        $objDataset->save();

    }
}
