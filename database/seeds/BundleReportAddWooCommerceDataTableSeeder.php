<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BundleReportAddWooCommerceDataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table ( "bundle_report" )->delete ();

        $wooCommerce = new    \App\Services\WooCommerceService();
        $investor = $wooCommerce->getBundleDetail ( 12928 );//Investor Exclusive Offer: Investor Bundle
        $opertor = $wooCommerce->getBundleDetail ( 13047 );//Operator Exclusive Offer: Operator Bundle
        $researcher = $wooCommerce->getBundleDetail ( 12937 );//Researcher Exclusive Offer: Researcher Bundle
        $buildOwnBundle = $wooCommerce->getBundleDetail ( 12914 );//Build Your Own Bundle

        if ( $investor[ 'name' ] == "Investor Exclusive Offer: Investor Bundle" ) {
            $this->insertBundleData ( $investor[ 'bundle' ], 12928 );

        }

        if ( $opertor[ 'name' ] == "Operator Exclusive Offer: Operator Bundle" ) {
            $this->insertBundleData ( $opertor[ 'bundle' ], 13047 );

        }
        if ( $researcher[ 'name' ] == "Researcher Exclusive Offer: Researcher Bundle" ) {

            $this->insertBundleData ( $researcher[ 'bundle' ], 12937 );

        }
        if ( $buildOwnBundle[ 'name' ] == "Build Your Own Bundle" ) {

            $this->insertBundleData ( $buildOwnBundle[ 'bundle' ], 12914 );

        }

    }

    function insertBundleData($data, $WooId)
    {

        foreach ( $data as $rows ) {
            $arr = ['report_id' => 0, 'bundle_id' => 0, 'mandatory' => 1, 'bundle_woo_id' => $WooId, 'bundled_item_id' => $rows[ 'bundled_item_id' ], 'bundle_product_id' => $rows[ 'product_id' ]];
            DB::table ( "bundle_report" )->insert ( [$arr] );
        }
    }
}


