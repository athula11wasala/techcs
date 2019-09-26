<?php

use Illuminate\Database\Seeder;
use App\Models\Interest;

class UpdateInterstTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::delete ( DB::raw ( "
               DELETE n1 FROM interest n1, interest n2 WHERE n1.id > n2.id AND n1.name = n2.name
        " ) );


         $interst1 = Interest::where("name",'Calfornia')->first();
         if(!empty($interst1)){
             $interst1->name = "California";
             $interst1->save();


         }

        $interst2 = Interest::where("name",'Consumer,brand & product insight')->first();
        if(!empty($interst2)){

            $interst2->name = "Consumer, brand & product insigh";
            $interst2->save();

        }
        $interst3 = Interest::where("name",'Meidcal & scientific')->first();
        if(!empty($interst3)){

            $interst3->name = "Medical & scientific";
            $interst3->save();

        }




    }
}


