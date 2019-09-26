<?php

use Illuminate\Database\Seeder;
use  App\Models\ZeferRetailPrice;


class ZeferRetailPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->save ();
        //$checkExsit_record = DB::table ( "zefer_retail_price" )->where ( "market", "Medical" )->where ( "state", "AZ" )->where ( "product", "Flower" )->where ( "strain", "Hybrid" )
        //  ->where ( "price_ounce", 85.9 )->first ();

        //if ( empty( $checkExsit_record ) ) {

        //  $this->save ();
        //}

    }

    public function save()
    {

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "AZ";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 85.9;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "CA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 72.97;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "CO";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 77.51;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "MI";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 77.51;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "MT";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 77.51;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "OR";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 65;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "WA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 80;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "AZ";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 75.67;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "CA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 95.33;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "CO";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 73.49;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "MI";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 83.73;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "OR";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 79.1;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "WA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 72.12;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "AZ";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 84.88;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "CA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 80.25;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "CO";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Sativa";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 81.75;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "MI";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Sativa";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 82.67;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "OR";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Sativa";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 65.14;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Medical";
        $objZeferRetailPrice->state = "WA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Sativa";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 75.67;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Hybrid";
        $objZeferRetailPrice->state = "CA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 58.33;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Hybrid";
        $objZeferRetailPrice->state = "OR";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 78.75;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Hybrid";
        $objZeferRetailPrice->state = "CA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 75.67;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Hybrid";
        $objZeferRetailPrice->state = "OR";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 47.09;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Hybrid";
        $objZeferRetailPrice->state = "CA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Sativa";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 75;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "AK";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 92;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "CA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 73.24;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "CO";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 82.99;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "NV";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 80;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "OR";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 76.36;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "WA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Hybrid";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 69.85;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "CA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 92;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "CA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 86.14;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "CO";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 83.72;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "OR";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 71.75;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "WA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Indica";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 69.85;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "CA";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Sativa";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 91;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();

        $objZeferRetailPrice = new  ZeferRetailPrice();
        $objZeferRetailPrice->market = "Adult Use";
        $objZeferRetailPrice->state = "CO";
        $objZeferRetailPrice->product_category = "Flower";
        $objZeferRetailPrice->sub_type = "Sativa";
        $objZeferRetailPrice->quntity_type = "ounce";
        $objZeferRetailPrice->avg_price = 84.41;
        $objZeferRetailPrice->min_price = 60;
        $objZeferRetailPrice->max_price = 105;
        $objZeferRetailPrice->date = "2018-11-02";
        $objZeferRetailPrice->save ();


    }
}
