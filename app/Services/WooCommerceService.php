<?php

namespace App\Services;

use App\Equio\Exceptions\EquioException;
use App\Models\Report;
use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Empty_;
use Illuminate\Support\Facades\Auth;


class WooCommerceService
{

    private $response;

    public function __construct($version = null)
    {
        if ( $version == 'v3' ) {
            $this->woocommerce = new Client(
                Config::get ( 'custom_config.WOOCOMMERCE_API_URL' ),
                Config::get ( 'custom_config.WOOCOMMERCE_API_KEY' ),
                Config::get ( 'custom_config.WOOCOMMERCE_API_SECRET' ),
                ['wp_api' => true, 'version' => '/v3', 'wp_api_prefix' => '/wc-api']

            );
        } else {
            $this->woocommerce = new Client(
                Config::get ( 'custom_config.WOOCOMMERCE_API_URL' ),
                Config::get ( 'custom_config.WOOCOMMERCE_API_KEY' ),
                Config::get ( 'custom_config.WOOCOMMERCE_API_SECRET' ),
                ['wp_api' => true, 'version' => 'wc/v1',]
            );
        }
    }


    /**
     * Woo-commerce common retrieve.
     *
     * @param string $modal
     * @param array $searchArr
     * @return array
     */
    public function retrieveWooData($modal = '', $searchArr = [])
    {
        try {
            return $this->woocommerce->get ( $modal, $searchArr );
        } catch (HttpClientException $e) {
            throw new EquioException( $e->getMessage () );
        } catch
        (\Exception $e) {

            //throw new EquioException($e->getMessage());
            return false;
        }
    }


    /**
     * Woo-commerce common update.
     *
     * @param $modal
     * @param $searchArr
     * @return array
     */
    public function updateWooData($modal, $searchArr)
    {
        try {
            return $this->woocommerce->put ( $modal, $searchArr );
        } catch (HttpClientException $e) {
            throw new EquioException( $e->getMessage () );
        } catch
        (\Exception $e) {
            throw new EquioException( $e->getMessage () );
        }
    }


    /**
     * get specific data from woocommerce api.
     *
     * @param $modal (orders,product)
     * @param $searchArr (null , ['search' => 'thilan@ceylonsolutions.com','per_page' => 50,'status' => 'completed'])
     * @param $nodeArr (['id'=>'','total'=>'','billing'=>['first_name'=>'','last_name'=>'']])
     * @return \Illuminate\Support\Collection
     */
    public function getSpecificWooData($modal, $searchArr, $nodeArr)
    {
        $responseData = collect ();
        $results = $this->retrieveWooData ( $modal, $searchArr );
        foreach ( $results as $result ) {
            $this->response = collect ();


            $this->responseParser ( $result, $nodeArr );
            $responseData->push ( $this->response );
        }
        return $responseData;

    }

    /**
     * parse response data to request data.
     *
     * @param $result
     * @param $nodeArr
     */
    public function responseParser($result, $nodeArr)
    {
        $result = (array)$result;
        if ( $nodeArr != null ) {
            foreach ( $nodeArr as $key => $value ) {
                if ( $value == '' ) {
                    $this->response->put ( $key, $result[ $key ] );
                } else {
                    $this->responseParser ( $result[ $key ], $value );
                }
            }
        }
    }


    public function getBundleDetail($id = 0)
    {
        $productList = $this->retrieveWooData ( 'products/' . $id, [] );

        \Log::info(" *************** GET BUNDLE DATA *******************" , [$productList]);

        $arrList = [];
        if ( !empty( $productList ) ) {
            $arrList [ 'id' ] = $productList->id;
            $arrList [ 'name' ] = $productList->name;
            $arrList [ 'status' ] = $productList->status;
            $arrList [ 'price' ] = $productList->price;
            $arrList [ 'regular_price' ] = $productList->regular_price;
            $arrList [ 'sale_price' ] = $productList->sale_price;

            $arrList[ 'bundle' ] = [];

            foreach ( $productList->bundled_items as $rows ) {
                if ( !empty( $rows->optional ) ) {
                    $optional = $rows->optional;
                } else {
                    $optional = false;
                }
                $objReports = DB::table("reports")->where ("woo_id", $rows->product_id )->select("name","cover","price")->first();
                $name = !empty( $objReports->name ) ? $objReports->name : 'null';
                $report_price = !empty( $objReports->price ) ? $objReports->price : 'null';
                $cover = !empty( $objReports->name ) ? Report::CoverImage ( $objReports->cover ) : 'null';
                $arrList[ 'bundle' ][] = ['bundled_item_id' => $rows->bundled_item_id, 'product_id' => $rows->product_id, 'optional' => $optional, "name" => $name, 'cover' => $cover,"report_price"=>$report_price];
            }

        } else {
            return null;
        }
        return $arrList;

    }

    /**
     * Woo-commerce common store.
     *
     * @param string $modal
     * @param array $data
     * @return array
     * @throws EquioException
     */
    public function storeWooData($modal = '', $data = [])
    {
        try {
            return $this->woocommerce->post ( $modal, $data );
        } catch (HttpClientException $e) {
            throw new EquioException( $e->getMessage () );
        } catch
        (\Exception $e) {

            throw new EquioException( $e->getMessage () );
            return false;
        }
    }

    /**
     * Woo-commerce common updateJsonData.
     *
     * @param $modal
     * @param $searchArr
     * @return array
     */
    public function updateJsonData($modal, $searchArr)
    {
        $this->woocommerce = new Client(
            Config::get ( 'custom_config.WOOCOMMERCE_API_URL' ),
            Config::get ( 'custom_config.WOOCOMMERCE_API_KEY' ),
            Config::get ( 'custom_config.WOOCOMMERCE_API_SECRET' ),
            ['wp_api' => true, 'version' => '', 'wp_api_prefix' => '/wp-json/wc/v3']
        );

        try {
            return $this->woocommerce->put ( $modal, $searchArr );
        } catch (HttpClientException $e) {
            throw new EquioException( $e->getMessage () );
        } catch
        (\Exception $e) {
            throw new EquioException( $e->getMessage () );
        }
    }

    /**
     * @return array
     */
    public function getSubscriptionPlan()
    {
        $category_id =  Config::get ( 'custom_config.WOOCOMMERCE_CATEGORY_ID' );
        $data = [];
        if(empty($category_id)){
            return  $data;
        }

        $productList = $this->getSpecificWooData ( 'products', ['category' => $category_id, 'per_page' => '100'],
            ['id' => '', 'name' => '', 'variations' => '', 'description' => '', 'status' => '', 'type' => '', 'description' => '', 'price' => '', 'price_html' => ''] );

        $i = 0;
        $paln = [];
        $month[ 'id' ] = null;
        $month[ 'price' ] = null;;
        $month[ 'type' ] = null;
        $month[ 'package' ] = null;
        $monthPrice = null;
        $year[ 'id' ] = null;
        $year[ 'price' ] = null;;
        $year[ 'type' ] = null;
        $year[ 'package' ] = null;
        $yearPrice = null;
        $plan = [];
        $subcriptoin_duration['billed_annulay'] = '';

        foreach ( $productList as $rows ) {

            $month = [];
            $year = [];
            $monthPrice = '';

            if ( !empty( $rows[ 'variations' ] ) ) {

                foreach ( $rows[ 'variations' ] as $rows_varition ) {

                    if ( $rows_varition->attributes[ 0 ]->option == "Monthly" ) {
                        $month[ 'id' ] = $rows_varition->id;
                        $month[ 'price' ] = $rows_varition->price;
                        $month[ 'type' ] = $monthtype = $rows_varition->attributes[ 0 ]->option;
                        $month[ 'label' ] = 'Monthly';
                        $month[ 'package' ] = $rows[ 'name' ];
                        $monthPrice = $rows_varition->price;;

                    }

                    if ( $rows_varition->attributes[ 0 ]->option == "Monthly Billing" ) {
                        $month[ 'id' ] = $rows_varition->id;
                        $month[ 'price' ] = $rows_varition->price;
                        $month[ 'type' ] = $monthtype = $rows_varition->attributes[ 0 ]->option;
                        $month[ 'label' ] = 'Monthly';
                        $month[ 'package' ] = $rows[ 'name' ];
                        $monthPrice = $rows_varition->price;;

                    }

                    if ( $rows_varition->attributes[ 0 ]->option == "Annual" ) {

                        $year[ 'id' ] = $rows_varition->id;
                        $year[ 'price' ] = $rows_varition->price;
                        $year[ 'type' ] = $monthtype = $rows_varition->attributes[ 0 ]->option;
                        $year[ 'label' ] = 'Annual';
                        $year[ 'package' ] = $rows[ 'name' ];
                        $yearPrice = $rows_varition->price;
                    }

                    if ( $rows_varition->attributes[ 0 ]->option == "Annual Billing" ) {

                        $year[ 'id' ] = $rows_varition->id;
                        $year[ 'price' ] = $rows_varition->price;
                        $year[ 'type' ] = $monthtype = $rows_varition->attributes[ 0 ]->option;
                        $year[ 'label' ] = 'Annual';
                        $year[ 'package' ] = $rows[ 'name' ];
                        $yearPrice = $rows_varition->price;
                    }


                }

            }


            else {


                $monthSubcriptionPrice = $rows['price']/12;

                $year_subcriptoin[ 'id' ] = $rows['id'];
                $year_subcriptoin[ 'price' ] = $rows['price'];
                $year_subcriptoin[ 'type' ] = $monthtype = 'Annual';
                $year_subcriptoin[ 'label' ] = 'Annual';
                $year_subcriptoin[ 'package' ] = $rows[ 'name' ];
                $subcriptoin_duration['billed_annulay'] = 'billed annually';
                $yearSubcriptoinPrice = $rows['price'];


                if(!empty($month_subcriptoin)){
                    $plan_subcription[]  = $month_subcriptoin;

                }
                if(!empty($year_subcriptoin)){
                    $plan_subcription[]  = $year_subcriptoin;
                }


            }

            if(!empty($month)){
                $plan[]  = $month;


            }
            if(!empty($year)){
                $plan[]  = $year;
            }

            if($rows[ 'name' ] != "Equio® Essential Subscription With Trial" )
            {

                if($rows['type'] == "variable-subscription"){
                    $data[ 'level' ][ $i ] = ['id' => $rows[ 'id' ],  'billed_annulay'=>  $subcriptoin_duration['billed_annulay'] , 'name' => $rows[ 'name' ], 'hover' => $rows[ 'description' ], 'type' => 'month', 'month_price' => $monthPrice, 'plan' => $plan];
                    $plan = [];

                    $i++;

                }else if($rows['type'] == "subscription"){

                    $data[ 'level' ][ $i ] = ['id' => $rows[ 'id' ],   'billed_annulay'=>  $subcriptoin_duration['billed_annulay'] ,  'name' => $rows[ 'name' ], 'hover' => $rows[ 'description' ], 'type' => 'month', 'month_price' => $monthSubcriptionPrice, 'plan' => $plan_subcription];
                    $plan_subcription = [];

                    $i++;

                }


           }



        }

        return $data;
    }
    /**
     * @return customerId
     */
    public function getCustomerId()
    {
        $customer_id = null;
        try {
            $customerDetails = $this->getSpecificWooData ( 'customers/', ['email' => Auth::user()->email], ['id' => '', 'email' => ''] );

            if(count($customerDetails) == 0){
                $customerDetails = $this->getSpecificWooData ( 'customers/', ['email' => Auth::user()->email,'role'=>'subscriber'], ['id' => '', 'email' => ''] );
            }

            if ( isset( $customerDetails ) ) {

                if ( isset( $customerDetails[ 0 ] ) ) {
                    $customer_id = $customerDetails[ 0 ][ 'id' ];
                }

            }

        } catch (HttpClientException $e) {
            throw new EquioException( $e->getMessage () );
        } catch
        (\Exception $e) {

            return false;
        }

        return $customer_id;
    }


}