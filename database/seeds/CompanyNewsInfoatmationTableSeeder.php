<?php

use Illuminate\Database\Seeder;
use App\Models\CompanyNewsInformaiton;

class CompanyNewsInfoatmationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objNews = new CompanyNewsInformaiton();
        $objNews->news_infomation_header = 1;
        $objNews->name ='Search engine';
        $objNews->save();

        $objNews2 = new CompanyNewsInformaiton();
        $objNews2->news_infomation_header = 1;
        $objNews2->name ='News article';
        $objNews2->save();

        $objNews3 = new CompanyNewsInformaiton();
        $objNews3->news_infomation_header = 1;
        $objNews3->name ='Banner ad';
        $objNews3->save();

        $objNews4 = new CompanyNewsInformaiton();
        $objNews4->news_infomation_header = 2;
        $objNews4->name ='MJBiz';
        $objNews4->save();

        $objNews5 = new CompanyNewsInformaiton();
        $objNews5->news_infomation_header = 2;
        $objNews5->name ='CWCBE';
        $objNews5->save();

        $objNews6 = new CompanyNewsInformaiton();
        $objNews6->news_infomation_header = 2;
        $objNews6->name ='ArcView';
        $objNews6->save();

        $objNews6 = new CompanyNewsInformaiton();
        $objNews6->news_infomation_header = 2;
        $objNews6->name ='NCIA';
        $objNews6->save();

        $objNews7 = new CompanyNewsInformaiton();
        $objNews7->news_infomation_header = 2;
        $objNews7->name ='CannaTech';
        $objNews7->save();

        $objNews8 = new CompanyNewsInformaiton();
        $objNews8->news_infomation_header = 2;
        $objNews8->name ='New West';
        $objNews8->save();

        $objNews9 = new CompanyNewsInformaiton();
        $objNews9->news_infomation_header = 2;
        $objNews9->name ='Other conference (please specify)';
        $objNews9->save();


        $objNews10 = new CompanyNewsInformaiton();
        $objNews10->news_infomation_header = 3;
        $objNews10->name ='Please specify (text box)';
        $objNews10->save();

        $objNews10 = new CompanyNewsInformaiton();
        $objNews10->news_infomation_header = 4;
        $objNews10->name ='Twitter';
        $objNews10->save();

        $objNews11 = new CompanyNewsInformaiton();
        $objNews11->news_infomation_header = 4;
        $objNews11->name ='Facebook';
        $objNews11->save();

        $objNews12 = new CompanyNewsInformaiton();
        $objNews12->news_infomation_header = 4;
        $objNews12->name ='Instagram';
        $objNews12->save();

        $objNews13 = new CompanyNewsInformaiton();
        $objNews13->news_infomation_header = 4;
        $objNews13->name ='LinkedIn';
        $objNews13->save();

        $objNews14 = new CompanyNewsInformaiton();
        $objNews14->news_infomation_header = 4;
        $objNews14->name ='Massroots';
        $objNews14->save();

        $objNews15 = new CompanyNewsInformaiton();
        $objNews15->news_infomation_header = 4;
        $objNews15->name ='Other (please specify)';
        $objNews15->save();

        $objNews16 = new CompanyNewsInformaiton();
        $objNews16->news_infomation_header = 5;
        $objNews16->name ='TV';
        $objNews16->save();

        $objNews17 = new CompanyNewsInformaiton();
        $objNews17->news_infomation_header = 5;
        $objNews17->name ='Publication/Book/Study';
        $objNews17->save();

        $objNews18 = new CompanyNewsInformaiton();
        $objNews18->news_infomation_header = 5;
        $objNews18->name ='News article';
        $objNews18->save();


        $objNews19 = new CompanyNewsInformaiton();
        $objNews19->news_infomation_header = 6;
        $objNews19->name ='Please specify so we may thank them! (text box)';
        $objNews19->save();

        $objNews20 = new CompanyNewsInformaiton();
        $objNews20->news_infomation_header = 7;
        $objNews20->name ='Please describe (text box)';
        $objNews20->save();

    }
}
