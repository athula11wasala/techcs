<?php

use Illuminate\Database\Seeder;
use  App\Models\CreditUser;

class CreditUseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $dataSet = ['Beau Whitney, Senior Economist, New Frontier Data', 'John Kagia, Chief Knowledge Officer, New Frontier Data',
            'Kacey Morrissey, Senior Industry Analyst, New Frontier Data', 'J.J. McCoy, Senior Copy Editor, New Frontier Data',
            'Gretchen Gailey, EVP, Communications & Government Affairs, New Frontier Data', 'Rob Kuvinka, Data Scientist, New Frontier Data',
            'Sean Murphy, DIrector, Hemp Analytics, New Frontier Data'];

        foreach ( $dataSet as $rows ) {

            $objDataset = new  CreditUser();
            $objDataset->name =   explode(',', $rows)[0];;
            $objDataset->description = $rows;
            $objDataset->type = 1;
            $objDataset->save ();

            $objDataset = new  CreditUser();
            $objDataset->name =   explode(',', $rows)[0];
            $objDataset->description = $rows;
            $objDataset->type = 2;
            $objDataset->save ();


            $objDataset = new  CreditUser();
            $objDataset->name =   explode(',', $rows)[0];
            $objDataset->description = $rows;
            $objDataset->type = 3;
            $objDataset->save ();


            $objDataset = new  CreditUser();
            $objDataset->name =   explode(',', $rows)[0];
            $objDataset->description = $rows;
            $objDataset->type = 4;
            $objDataset->save ();

        }

    }
}
