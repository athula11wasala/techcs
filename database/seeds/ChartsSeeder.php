<?php

use Illuminate\Database\Seeder;
use App\Models\Chart;

class ChartsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $chart = new Chart();
        $chart->reportname = '2016 Cannabis Investor Study';
        $chart->title = '2015-2016 MARIJUANA STOCK PERFORMANCE vs S&P500 and NASDAQ';
        $chart->chartfilename = 'storage/chart/chart-image/NFD-2016CannabisInvestorStudy-Chart01.jpg';
        $chart->reportfilename = 'storage/chart/enterprise-pdf/NFD-2016CannabisInvestorStudy-E.pdf';
        $chart->created_at = '2018-01-22 20:32:49';
        $chart->updated_at = '2018-01-22 20:32:49';
        $chart->report_id = 1;
        $chart->keywords = 1;
        $chart->save();

        $chart = new Chart();
        $chart->reportname = '2016 Cannabis Investor Study';
        $chart->title = 'INVESTOR LEVEL OF INDUSTRY ENGAGEMENT';
        $chart->chartfilename = 'storage/chart/chart-image/NFD-2016CannabisInvestorStudy-Chart02.jpg';
        $chart->reportfilename = 'storage/chart/enterprise-pdf/NFD-2016CannabisInvestorStudy-E.pdf';
        $chart->created_at = '2018-01-22 20:32:49';
        $chart->updated_at = '2018-01-22 20:32:49';
        $chart->report_id = 2;
        $chart->keywords = 2;
        $chart->save();


        $chart = new Chart();
        $chart->reportname = '2016 Cannabis Investor Study';
        $chart->title = 'INTEREST IN PLANT VS ANCILLARY BUSINESSES';
        $chart->chartfilename = 'storage/chart/chart-image/chart-image/NFD-2016CannabisInvestorStudy-Chart03.jpg';
        $chart->reportfilename = 'storage/chart/enterprise-pdf/NFD-2016CannabisInvestorStudy-E.pdf';
        $chart->created_at = '2018-01-22 20:32:49';
        $chart->updated_at = '2018-01-22 20:32:49';
        $chart->report_id = 3;
        $chart->keywords = 3;
        $chart->save();

        $chart = new Chart();
        $chart->reportname = '2016 Cannabis Investor Study';
        $chart->title = 'INVESTOR INTEREST BY CANNABIS INDUSTRY SEGMENT';
        $chart->chartfilename = 'storage/chart/chart-image/chart-image/NFD-2016CannabisInvestorStudy-Chart04.jpg';
        $chart->reportfilename = 'storage/chart/enterprise-pdf/NFD-2016CannabisInvestorStudy-E.pdf';
        $chart->created_at = '2018-01-22 20:32:49';
        $chart->updated_at = '2018-01-22 20:32:49';
        $chart->report_id = 4;
        $chart->keywords = 4;
        $chart->save();


        $chart = new Chart();
        $chart->reportname = '2016 Cannabis Investor Study';
        $chart->title = 'INTEREST IN ADULT USE, MEDICAL AND CBD MARKETS';
        $chart->chartfilename = 'storage/chart/chart-image/NFD-2016CannabisInvestorStudy-Chart05.jpg';
        $chart->reportfilename = 'storage/chart/enterprise-pdf/NFD-2016CannabisInvestorStudy-E.pdf';
        $chart->created_at = '2018-01-22 20:32:49';
        $chart->updated_at = '2018-01-22 20:32:49';
        $chart->report_id = 5;
        $chart->keywords = 5;
        $chart->save();

        $chart = new Chart();
        $chart->reportname = 'The Cannabis Industry Annual Report: 2017 Legal Marijuana Outlook';
        $chart->title = 'TOTAL U.S. LEGAL CANNABIS MARKET 2016 & 2025';
        $chart->chartfilename = 'storage/chart/chart-image/NFD-CIAR2017-Chart01.jpg';
        $chart->reportfilename = 'storage/chart/enterprise-pdf/NFD-CIAR2017-E.pdf';
        $chart->created_at = '2018-01-22 20:32:49';
        $chart->updated_at = '2018-01-22 20:32:49';
        $chart->report_id = 6;
        $chart->keywords = 6;
        $chart->save();

        $chart = new Chart();
        $chart->reportname = '2016 Legal Cannabis Market: California State Profile';
        $chart->title = 'A STRONG MAJORITY SUPPORTS MEDICAL MARIJUANA LEGISLATION';
        $chart->chartfilename = 'storage/chart/chart-image/NFD-2016LegalCannabisMarketCalifornia-Chart04.jpg';
        $chart->reportfilename = 'storage/chart/enterprise-pdf/NFD-2016LegalCannabisMarketCalifornia-E.pdf';
        $chart->created_at = '2018-01-22 20:32:49';
        $chart->updated_at = '2018-01-22 20:32:49';
        $chart->report_id = 7;
        $chart->keywords = 7;
        $chart->save();

    }
}

