<?php

use Illuminate\Database\Seeder;

class QualifyCondtion_Update_colums_TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $affected = DB::Connection("mysql_external_intake")->table('qualifying_conditions')
            ->where("id","!=",1)
            ->update(array('ptsd' => "T",'amyotrophic_sclerosis' => "T",'parkinson' => "T",'terminal' => "T",
                           'bipolar' => "T",'chronic_fatigue' => "T",'diabetes' => "T",'endometriosis_PMS' => "T",
                          'insomnia' => "T",'lyme' => "T",
                          'ocd' => "T",'rheumatoid' => "T",'sickle_anemia' => "T",'colitis' => "T"))
                           ;

        $affected_ = DB::Connection("mysql_external_intake")->table('qualifying_conditions')->where("id","=",1)
            ->update(array('ptsd' => "Post-traumatic stress disorder",'amyotrophic_sclerosis' => "Amyotrophic lateral sclerosis",'parkinson' => "Parkinson's disease",'terminal' => "Terminal illness",
        'bipolar' => "Bipolar disorder",'chronic_fatigue' => "Chronic fatigue syndrome",'diabetes' => "Diabetes",'endometriosis_PMS' => "Endometriosis/PMS",
        'insomnia' => "Insomnia/sleep disorders",'lyme' => "Lyme disease",
        'ocd' => "Obsessive compulsive disorder",'rheumatoid' => "Rheumatoid arthritis",'sickle_anemia' => "Sickle cell anemia",'colitis' => "Ulcerative colitis"))
        ;

    }
}



