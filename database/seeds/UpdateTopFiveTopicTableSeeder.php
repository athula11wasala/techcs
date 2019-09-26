<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateTopFiveTopicTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::Connection("mysql_cms")->table('top5')->where("topic","wildcard")->update(array('topic' => 'Wildcard'));

        DB::Connection("mysql_cms")->table('top5')->where("topic","legal")->update(array('topic' => 'Legal'));

        DB::Connection("mysql_cms")->table('top5')->where("topic","medical")->update(array('topic' => 'Medical'));

        DB::Connection("mysql_cms")->table('top5')->where("topic","social")->update(array('topic' => 'Social'));

        DB::Connection("mysql_cms")->table('top5')->where("topic","financial")->update(array('topic' => 'Financial'));

        DB::Connection("mysql_cms")->table('top5')->where("topic","hemp")->update(array('topic' => 'Hemp'));

        DB::Connection("mysql_cms")->table('top5')->where("topic","international")->update(array('topic' => 'International'));

        DB::Connection("mysql_cms")->table('top5')->where("topic","tech_sci_innov")->update(array('topic' => 'Tech, Science & Innovation'));

        DB::Connection("mysql_cms")->table('top5')->where("topic","infocus")->update(array('topic' => 'InFocus'));

    }
}
