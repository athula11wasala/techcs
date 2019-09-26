<?php

use Illuminate\Database\Seeder;
use App\Models\Webinar;
use Illuminate\Support\Facades\DB;

class WebinarsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table("webinars")->delete();

        $objWebnar = new Webinar();
        $objWebnar->period = "Q1 2018";
        $objWebnar->title = 'Cannabis & Taxes Webinar';
        $objWebnar->description_short = 'Cannabis & Taxes Webinar from New Frontier Data and Cohn Reznick';
        $objWebnar->description_long = 'In this webinar, New Frontier Data and Cohn Reznick discuss how to navigate the complex cannabis tax codes, how to avoid pitfalls and stay ahead in this ever-evolving landscape. Some points of interest are: Key trends that require sophisticated tax and accounting services Innovative ways that cannabis businesses can optimize their tax performance How to navigate California’s vast regulatory future';
        $objWebnar->duration = '1:08:50';
        $objWebnar->link ='https://youtu.be/B0HrwSk-VMU' ;
        $objWebnar->full_pdf = 'Q12018_Cannabis_Taxes-webinar.pdf';
        $objWebnar->save();

        $objWebnar2 = new Webinar();
        $objWebnar2->period = 'Q4 2017';
        $objWebnar2->title = '2017 Diversity in Cannabis Webinar';
        $objWebnar2->description_short = "2017 Diversity in Cannabis Webinar by New Frontier Data & Women Grow";
        $objWebnar2->description_long ='New Frontier Data along with Women Grow present a study on diversity in the cannabis industry.';
        $objWebnar2->duration = '0:58:49';
        $objWebnar2->link ='https://youtu.be/7A1iqZLGAik';
        $objWebnar2->full_pdf = 'Q42017_DiversityInCannabis-webinar.pdf';
        $objWebnar2->save();

        $objWebnar3 = new Webinar();
        $objWebnar3->period = 'Q3 2017';
        $objWebnar3->title = 'Cannabis On-Demand Webinar';
        $objWebnar3->description_short = 'Cannabis On Demand - Online Ordering Trends in California';
        $objWebnar3->description_long ='New Frontier Data along with greenRush present a study on cannabis  e-commerce and delivery service in California.';
        $objWebnar3->duration = '1:07:51';
        $objWebnar3->link = 'https://youtu.be/39s_1NS16D4' ;
        $objWebnar3->full_pdf = 'Q32017_CannabisOnDemand-webinar.pdf';
        $objWebnar3->save();

        $objWebnar4 = new Webinar();
        $objWebnar4->period = 'Q2 2017';
        $objWebnar4->title =  'Investing in the Cannabis Industry Webinar';
        $objWebnar4->description_short = 'Investing in Cannabis 2017 Risks & Opportunities';
        $objWebnar4->description_long = 'New Frontier Data along with Viridian Capital Advisors present an overview on investing in cannabis in 2017.';
        $objWebnar4->duration = '0:56:27';
        $objWebnar4->link ='https://youtu.be/NoCYFwzZf0I' ;
        $objWebnar4->full_pdf = 'Q22017_InvestingInCannabis-webinar.pdf';
        $objWebnar4->save();

        $objWebnar5 = new Webinar();
        $objWebnar5->period = 'Q1 2017';
        $objWebnar5->title = 'The Cannabis Industry Annual Report Webinar';
        $objWebnar5->description_short = 'Cannabis Industry Annual Report Webinar';
        $objWebnar5->description_long ='';
        $objWebnar5->duration = '1:08:24';
        $objWebnar5->link ='https://youtu.be/3ApvFNn6Ga4';
        $objWebnar5->full_pdf = 'Q12017_CIAR-webinar.pdf';
        $objWebnar5->save();

        $objWebnar6 = new Webinar();
        $objWebnar6->period = 'Q4 2016';
        $objWebnar6->title =  '2016 Cannabis Industry Year End Review';
        $objWebnar6->description_short = '2016 Cannabis Industry Year End Review';
        $objWebnar6->description_long ='';
        $objWebnar6->duration = '1:06:40';
        $objWebnar6->link = 'https://youtu.be/WsLJtE1wg5c' ;
        $objWebnar6->full_pdf ='Q42016_CannabisIndustryYearEndReview-webinar.pdf';
        $objWebnar6->save();

        $objWebnar7 = new Webinar();
        $objWebnar7->period = 'Q4 2016';
        $objWebnar7->title = 'Cannabis Industry 2.0';
        $objWebnar7->description_short = 'The Cannabis Industry 2.0 Webinar';
        $objWebnar7->description_long ='The next step up following the historic 2016 U.S. elections';
        $objWebnar7->duration = '0:39:50';
        $objWebnar7->link = 'https://youtu.be/YPdfI_08Inw' ;
        $objWebnar7->full_pdf = 'Q42016_CannabisIndustry2dot0-webinar.pdf';
        $objWebnar7->save();

        $objWebnar8 = new Webinar();
        $objWebnar8->period = 'Q3 2016';
        $objWebnar8->title = '2016 Legal Cannabis Markets: California State Profile';
        $objWebnar8->description_short =  '2016 Legal Cannabis Markets: California State Profile';
        $objWebnar8->description_long ='Full-length webinar by New Frontier Data and Arcview Market Research discussing their findings from their co-produced report,2016 Legal Cannabis Markets: California State Profile.';
        $objWebnar8->duration = '1:00:28';
        $objWebnar8->link =  'https://youtu.be/kGL6NQXM_vI' ;
        $objWebnar8->full_pdf = 'Q32016_LegalCannabisMarketsCalifornia-webinar.pdf';
        $objWebnar8->save();


        $objWebnar9 = new Webinar();
        $objWebnar9->period = 'Q3 2016';
        $objWebnar9->title = 'Cannabis Industry Overview & Investment Opportunities';
        $objWebnar9->description_short = 'Legal Marijuana Webinar by New Frontier Data';
        $objWebnar9->description_long = 'New Frontier Data, the leading data & analytics provider for legal cannabis, discusses legal markets across the U.S. in this full-length webinar.';
        $objWebnar9->duration = '0:29:28';
        $objWebnar9->link ='https://youtu.be/i5BIx2u9I_Q' ;
        $objWebnar9->full_pdf = 'Q32016_CannabisIndustryOverview_InvestmentOpportunities-webinar.pdf';
        $objWebnar9->save();

        $objWebnar10 = new Webinar();
        $objWebnar10->period = 'Q2 2016 ';
        $objWebnar10->title = '2016 Cannabis Industry Overview';
        $objWebnar10->description_short = "Legal Marijuana Webinar by New Frontier Data & Arcview Market Research";
        $objWebnar10->description_long ='';
        $objWebnar10->duration = '0:56:33';
        $objWebnar10->link ='https://youtu.be/FzZCZMBfZ3g' ;
        $objWebnar10->full_pdf = 'Q22016_CannabisIndustryOverview-webinar.pdf';
        $objWebnar10->save();

        $objWebnar11 = new Webinar();
        $objWebnar11->period = 'Q1 2016';
        $objWebnar11->title = '2016 Global Legal Marijuana Markets: A Worldwide View    ';
        $objWebnar11->description_short = '2016 Global Legal Marijuana Markets: A Worldwide View';
        $objWebnar11->description_long ='With marijuana legalization often being approached in piecemeal and patchwork fashion, John Kagia and Sam Osborn shed light on the current approaches to marijuana regulation in key markets. They adopt a global perspective to identify the top developments that will shape the growth and trajectory of legal marijuana markets around the world in the year ahead. These are the countries that New Frontier examines in the report:  Australia, Canada, Colombia, Israel, Germany, Jamaica, Mexico, Spain and Uruguay.';
        $objWebnar11->duration = '0:29:29';
        $objWebnar11->link = 'https://youtu.be/m4AajXplvTg' ;
        $objWebnar11->full_pdf = 'Q12016_GlobalLegalMarijuanaMarkets-webinar.pdf';
        $objWebnar11->save();


    }
}