<?php

use Illuminate\Database\Seeder;
use App\Models\DataSet;
use Carbon\Carbon;



class DataSetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $objDataset = new DataSet();
        $objDataset->data_set = 1;
        $objDataset->description ='2018 first quarter';
        $objDataset->from =  Carbon::parse('2018-01-01');
        $objDataset->to = Carbon::parse('2018-03-31');
        $objDataset->created_at = '2018-03-01 18:30:00';
        $objDataset->updated_at = '2018-03-31 18:31:00';
        $objDataset->save();

        $objDataset = new DataSet();
        $objDataset->data_set = 1;
        $objDataset->description ='2018 second quarter';
        $objDataset->from =  Carbon::parse('2018-04-01');
        $objDataset->to = Carbon::parse('2018-06-30');
        $objDataset->created_at = '2018-03-01 18:30:00';
        $objDataset->updated_at = '2018-03-31 18:31:00';
        $objDataset->save();


        $objDataset = new DataSet();
        $objDataset->data_set =2;
        $objDataset->description ='2018 first quarter';
        $objDataset->from =  Carbon::parse('2018-01-01');
        $objDataset->to = Carbon::parse('2018-03-31');
        $objDataset->created_at = '2018-03-01 18:30:00';
        $objDataset->updated_at = '2018-03-31 18:31:00';
        $objDataset->save();



        $objDataset = new DataSet();
        $objDataset->data_set = 3;
        $objDataset->description ='2018 first quarter';
        $objDataset->from =  Carbon::parse('2018-01-01');
        $objDataset->to = Carbon::parse('2018-03-31');
        $objDataset->created_at = '2018-03-01 18:30:00';
        $objDataset->updated_at = '2018-03-31 18:31:00';
        $objDataset->save();

        $objDataset = new DataSet();
        $objDataset->data_set = 4;
        $objDataset->description ='2018 first quarter';
        $objDataset->from =  Carbon::parse('2018-01-01');
        $objDataset->to = Carbon::parse('2018-03-31');
        $objDataset->created_at = '2018-03-01 18:30:00';
        $objDataset->updated_at = '2018-03-31 18:31:00';
        $objDataset->save();


    }
}
