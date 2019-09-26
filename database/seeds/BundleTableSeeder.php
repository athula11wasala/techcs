<?php

use Illuminate\Database\Seeder;
use App\Models\Bundle;
use App\Models\BundleReport;


class BundleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table ( 'bundle' )->delete ();
        DB::table ( 'bundle_report' )->delete ();

        $bundleOne = new Bundle();
        $bundleOne->name = "Investor Exclusive Offer: Investor Bundle";
        $bundleOne->woo_id = 12928;
        $bundleOne->price = '';
        $bundleOne->description = "";
        $bundleOne->cover_image = "";
        $bundleOne->purchase_url_link = "https://newfrontierdata.com/product/investor-bundle-2/";
        $bundleOne->status = 1;
        $bundleOne->save ();

        if ( $bundleOne ) {

            $this->bundleOne ( $bundleOne );

        }

        $bundleTwo = new Bundle();
        $bundleTwo->name = "Operator Exclusive Offer: Operator Bundle";
        $bundleTwo->woo_id = 13047;
        $bundleTwo->price = '';
        $bundleTwo->description = "";
        $bundleTwo->cover_image = "";
        $bundleTwo->purchase_url_link = "https://newfrontierdata.com/product/operator-bundle/";
        $bundleTwo->status = 1;
        $bundleTwo->save ();

        if ( $bundleTwo ) {

            $this->bundleTwo ( $bundleTwo );

        }


        $bundleThree = new Bundle();
        $bundleThree->woo_id = 12937;
        $bundleThree->name = "Researcher Exclusive Offer: Researcher Bundle";
        $bundleThree->description = "";
        $bundleThree->price = '';
        $bundleThree->status = 1;
        $bundleThree->cover_image = "";
        $bundleThree->purchase_url_link = "https://newfrontierdata.com/product/researcher-bundle/";
        $bundleThree->save ();

        if ( $bundleThree ) {

            $this->bundleThree ( $bundleThree );

        }

    }

    public function bundleOne($bundleOne)
    {

        $bundleReportOne = new BundleReport();
        $bundleReportOne->report_id = 27;
        $bundleReportOne->bundle_id = $bundleOne->id;
        $bundleReportOne->save ();

        $bundleReportTwo = new BundleReport();
        $bundleReportTwo->report_id = 31;
        $bundleReportTwo->bundle_id = $bundleOne->id;
        $bundleReportTwo->save ();

        $bundleReportThree = new BundleReport();
        $bundleReportThree->report_id = 3;
        $bundleReportThree->bundle_id = $bundleOne->id;
        $bundleReportThree->save ();

        $bundleReportFour = new BundleReport();
        $bundleReportFour->report_id = 7;
        $bundleReportFour->bundle_id = $bundleOne->id;
        $bundleReportFour->save ();

        $bundleReportFive = new BundleReport();
        $bundleReportFive->report_id = 17;
        $bundleReportFive->bundle_id = $bundleOne->id;
        $bundleReportFive->save ();

        $bundleReportSix = new BundleReport();
        $bundleReportSix->report_id = 26;
        $bundleReportSix->bundle_id = $bundleOne->id;
        $bundleReportSix->save ();

        $bundleReportSeven = new BundleReport();
        $bundleReportSeven->report_id = 5;
        $bundleReportSeven->bundle_id = $bundleOne->id;
        $bundleReportSeven->save ();

        $bundleReportEight = new BundleReport();
        $bundleReportEight->report_id = 28;
        $bundleReportEight->bundle_id = $bundleOne->id;
        $bundleReportEight->save ();

        $bundleReportNine = new BundleReport();
        $bundleReportNine->report_id = 29;
        $bundleReportNine->bundle_id = $bundleOne->id;
        $bundleReportNine->save ();


        $bundleReportTen = new BundleReport();
        $bundleReportTen->report_id = 30;
        $bundleReportTen->bundle_id = $bundleOne->id;
        $bundleReportTen->save ();

        $bundleReportEleven = new BundleReport();
        $bundleReportEleven->report_id = 33;
        $bundleReportEleven->bundle_id = $bundleOne->id;
        $bundleReportEleven->save ();

        $bundleReportTwelve = new BundleReport();
        $bundleReportTwelve->report_id = 32;
        $bundleReportTwelve->bundle_id = $bundleOne->id;
        $bundleReportTwelve->save ();

        $bundleReportThirteen = new BundleReport();
        $bundleReportThirteen->report_id = 18;
        $bundleReportThirteen->bundle_id = $bundleOne->id;
        $bundleReportThirteen->save ();

    }

    public function bundleTwo($bundleTwo)
    {

        $bundleReportOne = new BundleReport();
        $bundleReportOne->report_id = 27;
        $bundleReportOne->bundle_id = $bundleTwo->id;
        $bundleReportOne->save ();

        $bundleReportTwo = new BundleReport();
        $bundleReportTwo->report_id = 26;
        $bundleReportTwo->bundle_id = $bundleTwo->id;
        $bundleReportTwo->save ();

        $bundleReportThree = new BundleReport();
        $bundleReportThree->report_id = 5;
        $bundleReportThree->bundle_id = $bundleTwo->id;
        $bundleReportThree->save ();

        $bundleReportFour = new BundleReport();
        $bundleReportFour->report_id = 28;
        $bundleReportFour->bundle_id = $bundleTwo->id;
        $bundleReportFour->save ();


        $bundleReportFive = new BundleReport();
        $bundleReportFive->report_id = 29;
        $bundleReportFive->bundle_id = $bundleTwo->id;
        $bundleReportFive->save ();

        $bundleReportsix = new BundleReport();
        $bundleReportFive->report_id = 30;
        $bundleReportFive->bundle_id = $bundleTwo->id;
        $bundleReportFive->save ();

        $bundleReportseven = new BundleReport();
        $bundleReportseven->report_id = 33;
        $bundleReportseven->bundle_id = $bundleTwo->id;
        $bundleReportseven->save ();

    }

    public function bundleThree($bundleThree)
    {

        $bundleReportOne = new BundleReport();
        $bundleReportOne->report_id = 27;
        $bundleReportOne->bundle_id = $bundleThree->id;
        $bundleReportOne->save ();

        $bundleReportTwo = new BundleReport();
        $bundleReportTwo->report_id = 4;
        $bundleReportTwo->bundle_id = $bundleThree->id;
        $bundleReportTwo->save ();

        $bundleReportThree = new BundleReport();
        $bundleReportThree->report_id = 26;
        $bundleReportThree->bundle_id = $bundleThree->id;
        $bundleReportThree->save ();

        $bundleReportFour = new BundleReport();
        $bundleReportFour->report_id = 5;
        $bundleReportFour->bundle_id = $bundleThree->id;
        $bundleReportFour->save ();

        $bundleReportFive = new BundleReport();
        $bundleReportFive->report_id = 28;
        $bundleReportFive->bundle_id = $bundleThree->id;
        $bundleReportFive->save ();

        $bundleReportSix = new BundleReport();
        $bundleReportSix->report_id = 29;
        $bundleReportSix->bundle_id = $bundleThree->id;
        $bundleReportSix->save ();

        $bundleReportSeven = new BundleReport();
        $bundleReportSeven->report_id = 30;
        $bundleReportSeven->bundle_id = $bundleThree->id;
        $bundleReportSeven->save ();

        $bundleReportEight = new BundleReport();
        $bundleReportEight->report_id = 33;
        $bundleReportEight->bundle_id = $bundleThree->id;
        $bundleReportEight->save ();

    }


}


