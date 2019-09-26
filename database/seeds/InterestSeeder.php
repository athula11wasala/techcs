<?php

use Illuminate\Database\Seeder;
use App\Models\Interest;

class interestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $interst = new Interest();
        $interst->name = "Investment";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Energy & environmental studies";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Tax & regulation";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Technology";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "General U.S market research";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Meidcal & scientific";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Competitive analysis";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Academic";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Consumer,brand & product insight";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Distribution & packaging";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Strategic & business planning";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Extraction & processing";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "International market research";
        $interst->type = 1;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Other";
        $interst->type = 1;
        $interst->save();


        $interst = new Interest();
        $interst->name = "Colorado";
        $interst->type = 2;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Oregon";
        $interst->type = 2;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Washington";
        $interst->type = 2;
        $interst->save();

        $interst = new Interest();
        $interst->name = "Calfornia";
        $interst->type = 2;
        $interst->save();


    }
}
