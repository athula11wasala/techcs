<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\WooCommerceService;
use App\Traits\CouponCodeValidators;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserStripeRepository;
use App\Repositories\ReportPurchaseRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Events\EventWooCoomerceOrder;
use App\Models\User;
use DateTime;
use Mockery\Exception;


class CouponcodeController extends ApiController
{

    use CouponCodeValidators;

    private $error = 'error';
    private $message = 'message';

    /**
     * @var UserService
     */
    private $paymentService;
    private $wooCommerceService;
    private $userStripeRepository;
    private $reportPurchaseRepository;
    private $userRepository;
    private $fixed_product_cust_sel_id = [];

    /**
     * PaymentController constructor.
     * @param UserService $userService
     */
    public function __construct(WooCommerceService $wooCommerceService, UserStripeRepository $userStripeRepository, ReportPurchaseRepository $reportPurchaseRepository, UserRepository $userRepository)
    {
        $this->wooCommerceService = $wooCommerceService;
        $this->userStripeRepository = $userStripeRepository;
        $this->reportPurchaseRepository = $reportPurchaseRepository;
        $this->userRepository = $userRepository;
    }

    public function addCouponToCartInfoReqest(Request $request)
    {
        $coupon_info = $this->addCouponToCartInfoDetails ( $request );
        if ( isset( $coupon_info[ 'error' ] ) ) {
            return response ()->json ( ['error' => $coupon_info[ 'error' ]], 400 );
        }
        return $coupon_info;
    }

    public function createCouponSelectProdId($product_arr, $product_ids = [], $discount_type = '')
    {
        $ret = [];
        $price = 0;
        if ( !empty( $product_arr ) ) {

            foreach ( $product_arr as $row ) {

                if ( $discount_type == "fixed_cart" ) {

                    if ( $row[ 'qty' ] > 0 ) {

                        $objreports = DB::table ( "reports" )->where ( "woo_id", $row[ 'id' ] )->select ( "price" )->first ();
                        $price = $price + ($objreports->price * $row[ 'qty' ]);
                        for ( $x = 1; $x <= $row[ 'qty' ]; $x++ ) {

                            $ret [] = $row[ 'id' ];  //['id'=> $row[ 'id' ],'qty'=>$row[ 'qty' ]];

                        }

                    }

                } else {

                    if ( in_array ( $row[ 'id' ], $product_ids ) ) {


                        if ( $row[ 'qty' ] > 0 ) {

                            $objreports = DB::table ( "reports" )->where ( "woo_id", $row[ 'id' ] )->select ( "price" )->first ();
                            $price = $price + ($objreports->price * $row[ 'qty' ]);
                            for ( $x = 1; $x <= $row[ 'qty' ]; $x++ ) {

                                $ret [] = $row[ 'id' ];  //['id'=> $row[ 'id' ],'qty'=>$row[ 'qty' ]];

                            }

                        }

                    }

                }

            }
        }
        $data[ 'price' ] = $price;
        $data[ 'select_product' ] = $ret;
        return $data;
    }


    public function in_array_r($needle, $haystack, $strict = false)
    {
        foreach ( $haystack as $item ) {
            if ( ($strict ? $item === $needle : $item == $needle) || (is_array ( $item ) && $this->in_array_r ( $needle, $item, $strict )) ) {
                return true;
            }
        }

        return false;
    }


    public function onlyExcludeItemsWithFixCart($product_data, $exclude_product_ids, $exclude_prod_category)
    {
        $fixed_product_cust_sel_id = [];
        $remove_product_ids = [];
        if ( !empty( $exclude_prod_category ) ) {

            foreach ( $product_data as $rows ) {
                $product_categories = '';
                try {
                    $obj_list = $this->wooCommerceService->retrieveWooData ( 'products/' . $rows[ 'id' ] );
                } catch (\Exception $ex) {
                    $return_arr = ['error' => $ex->getMessage ()];
                    return $return_arr;
                }
                $product_categories_val = isset( $obj_list->categories ) ? $obj_list->categories : '';
                $product_categories_arr = [];

                foreach ( $product_categories_val as $val_rows ) {
                    $product_categories_arr [] = $val_rows->id;
                }

                if ( !empty( $product_categories_arr ) ) {

                    foreach ( $exclude_prod_category as $val ) {

                        if ( in_array ( $val, $product_categories_arr ) ) {

                            $remove_product_ids [] = $rows[ 'id' ];
                        }

                    }

                }

            }

        }

        if ( !empty( $exclude_product_ids ) ) {
            foreach ( $exclude_product_ids as $exclude_product_val ) {

                if ( !in_array ( $exclude_product_val, $remove_product_ids ) ) {

                    $remove_product_ids [] = $exclude_product_val;
                }

            }

        }
        if ( !empty( $remove_product_ids ) ) {
            foreach ( $product_data as $value_products ) {

                if ( !in_array ( $value_products[ 'id' ], $remove_product_ids ) ) {

                    $fixed_product_cust_sel_id[] = ['id' => $value_products[ 'id' ], 'qty' => $value_products[ 'qty' ]];
                }

            }

        }

        return $fixed_product_cust_sel_id;

    }

    public function addCouponToCartInfoDetails($request)
    {
        \Log::info ( "====AddCouponToCartInfoReqest->addCouponToCartInfoDetails" );
        $return_arr = [];
        $request_info = $request->all ();
        $product_arr = $request_info[ 'product' ];
        $payment_amount = $request_info[ 'payment_amount' ];
        $coupon_code = $request_info[ 'coupon_code' ];
        $return_arr[ 'prod_woo_id' ] = [];

        if ( empty( $coupon_code ) ) {

            $return_arr = ['error' => 'Please add coupon code'];
            return $return_arr;
        }

        try {
            $productList = $this->wooCommerceService->getSpecificWooData ( 'coupons', ['code' => $coupon_code, 'per_page' => '100'],
                [    'id' => '',
                    'code' => '',
                    'date_created' => '',
                    'date_modified' => '',
                    'discount_type' => '',
                    'description' => '',
                    'amount' => '',
                    'expiry_date' => '',
                    'usage_count' => '',
                    'individual_use' => '',
                    'product_ids' => '',
                    'exclude_product_ids' => '',
                    'usage_limit' => '',
                    'usage_limit_per_user' => '',
                    'limit_usage_to_x_items' => '',
                    'free_shipping' => '',
                    'product_categories' => '',
                    'excluded_product_categories' => '',
                    'exclude_sale_items' => '',
                    'minimum_amount' => '',
                    'maximum_amount' => '',
                    'email_restrictions' => '',
                    'used_by' => ''
                ] );

        } catch (Exception $ex) {
            $return_arr = ['error' => $ex->getMessage ()];
            return $return_arr;
        }
        \Log::info ( "AddCouponToCartInfoReqest->addCouponToCartInfoDetails", ['coupon_info' => json_encode ( $productList, JSON_PRETTY_PRINT )] );
        if ( count ( $productList ) > 0 ) {

            $email_restriction = $productList[ 0 ][ 'email_restrictions' ];
            if ( !empty( $email_restriction ) ) {
                if ( !in_array ( Auth::user ()->email, $email_restriction ) ) {

                    $return_arr = ['error' => 'This coupon code is restrict for this email'];
                    return $return_arr;
                }

            }
            $coupon_id = $productList[ 0 ][ 'id' ];
            $minimum_amount = $productList[ 0 ][ 'minimum_amount' ];
            $maximum_amount = $productList[ 0 ][ 'maximum_amount' ];
            $discount_type = $productList[ 0 ][ 'discount_type' ];
            $usage_count = $productList[ 0 ][ 'usage_count' ];
            $product_ids = $productList[ 0 ][ 'product_ids' ];
            $exclude_product_ids = $productList[ 0 ][ 'exclude_product_ids' ];
            $exclude_prod_category = $productList[ 0 ][ 'excluded_product_categories' ];
            $include_prod_category = $productList[ 0 ][ 'product_categories' ];
            $include_prod_category_flag = false;
            $include_cat_base_prod_id = [];
            $fixed_product_flag = false;
            $fixed_product_cust_sel_id = [];
            $custProduct = [];

            if ( !empty( $include_prod_category ) ) {
                foreach ( $include_prod_category as $catgory ) {

                    foreach ( $product_arr as $rows ) {

                        $objReport = $this->wooCommerceService->retrieveWooData ( 'products/' . $rows[ 'id' ] );

                        foreach ( $objReport->categories as $row_categories ) {

                            if ( $row_categories->id == $catgory ) {
                                $include_prod_category_flag = true;
                                $include_cat_base_prod_id[] = ['id' => $rows[ 'id' ], 'qty' => $rows[ 'qty' ]];
                            }

                        }

                    }

                }


                if ( $include_prod_category_flag == false ) {
                    $return_arr = ['error' => 'Sorry, this coupon is not applicable to selected products.'];
                    return $return_arr;
                }

            }

            if ( !empty( $exclude_product_ids ) ) {

                foreach ( $exclude_product_ids as $exclude_product_id ) {

                    foreach ( $product_arr as $rows ) {

                        if ( $rows[ 'id' ] == $exclude_product_id ) {

                            if ( $discount_type == "fixed_cart" ) {
                                $objreports = DB::table ( "reports" )->where ( "woo_id", $exclude_product_id )->select ( "name" )->first ();
                                $return_arr = ['error' => "Sorry, this coupon is not applicable to the products: " . $objreports->name];
                                return $return_arr;
                            }

                        }


                    }


                }


            }

            if ( !empty( $product_ids ) ) {

                foreach ( $product_arr as $ret ) {
                    $custProduct[] = $ret[ 'id' ];

                    if ( in_array ( $ret[ 'id' ], $product_ids ) ) {
                        $fixed_product_flag = true;
                        $fixed_product_cust_sel_id [] = ['id' => $ret[ 'id' ], 'qty' => $ret[ 'qty' ]];
                    } else {
                    }
                    $custProduct = null;
                }
            }
            if ( !empty( $product_ids ) ) {

                $this->fixed_product_cust_sel_id = $fixed_product_cust_sel_id;
                if ( count ( $fixed_product_cust_sel_id ) != count ( $product_ids ) ) {
                    $return_arr = ['error' => 'Sorry, this coupon is not applicable to selected products.'];
                    return $return_arr;
                }

                if ( $fixed_product_flag == false ) {
                    $return_arr = ['error' => 'coupon product not in cart'];
                    return $return_arr;

                }

            }
            $expiry_date = date ( 'Y-m-d', strtotime ( $productList[ 0 ][ 'expiry_date' ] ) );
            if ( !empty( $productList[ 0 ][ 'expiry_date' ] ) ) {

                $now = date ( 'Y-m-d' );
                if ( $expiry_date < $now ) {

                    $return_arr = ['error' => 'This coupon code  is expired'];
                    return $return_arr;
                }

            }
            $usage_limit_per_user = $productList[ 0 ][ 'usage_limit_per_user' ];
            $usage_limit = $productList[ 0 ][ 'usage_limit' ];
            $used_by = $productList[ 0 ][ 'used_by' ];
            $woocommerce_discount = $productList[ 0 ][ 'amount' ];
            $use_user_count = 0;
            $calcualte_discount = 0;

            if ( !empty( $usage_limit ) ) {

                if ( !empty( $usage_count ) ) {

                    if($usage_limit  == $usage_count ){

                        $return_arr = ['error' => 'Coupon usage limit has been reached.'];
                        return $return_arr;
                    }


                }

                if ( in_array ( Auth::user ()->email, $used_by ) ) {

                    $counts = array_count_values ( $used_by );
                    $use_user_count = $counts[ Auth::user ()->email ];

                    if ( empty( $usage_limit_per_user ) ) {
                        if ( !empty( $usage_limit ) ) {
                            if ( $usage_limit == $use_user_count || $use_user_count >= $usage_limit ) {

                                $return_arr = ['error' => 'Personal coupon usage is limit reached'];
                                return $return_arr;
                            }
                        }


                    } else {

                        if ( $use_user_count >= $usage_limit_per_user || $usage_limit_per_user == $use_user_count ) {

                            $return_arr = ['error' => 'Coupon usage limit is reached'];
                            return $return_arr;
                        }

                    }

                }
            } else {


                if ( !empty( $usage_limit_per_user ) ) {
                    if ( in_array ( Auth::user ()->email, $used_by ) ) {
                        $counts = array_count_values ( $used_by );
                        $use_user_count = $counts[ Auth::user ()->email ];
                        if ( $use_user_count >= $usage_limit_per_user || $usage_limit_per_user == $use_user_count ) {
                            $return_arr = ['error' => 'Coupon usage limit is reached'];
                            return $return_arr;
                        }

                    }

                }

            }
            \Log::info ( "==== AddCouponToCartInfoReqest->addCouponToCartInfoDetails DiscountType:" . $discount_type );

            switch ($discount_type) {
                case 'fixed_product':

                    if ( empty( $fixed_product_cust_sel_id ) ) {

                        $fixed_product_cust_sel_id = $include_cat_base_prod_id;
                    } else {
                        foreach ( $include_cat_base_prod_id as $rows ) {

                            $flag = $this->in_array_r ( $rows[ 'id' ], $fixed_product_cust_sel_id );
                            if ( $flag == false ) {
                                $fixed_product_cust_sel_id [] = $rows;
                            }
                        }

                    }
                    if ( empty( $fixed_product_cust_sel_id ) ) {
                        $fixed_product_info =  $product_arr;  // $this->onlyExcludeItemsWithFixCart ( $product_arr, $exclude_product_ids, $exclude_prod_category );
                        if(isset($fixed_product_info['error'])){
                            $return_arr = ['error' => $fixed_product_info['error']];
                            return $return_arr;
                        }
                        $fixed_product_cust_sel_id = $fixed_product_info;

                    }
                    $fixedProductDiscountDetails = $this->fixedProductDiscountDetails ( $fixed_product_cust_sel_id, $woocommerce_discount, 'fixed_product' );
                    $calcualte_discount = $fixedProductDiscountDetails[ 'discount' ];
                    $chckpaybleamount = $fixedProductDiscountDetails[ 'all_price' ];
                    $return_arr[ 'prod_woo_id' ] = $fixedProductDiscountDetails[ 'prod_woo_id' ];
                    break;
                case 'fixed_cart':
                    $calcualte_discount = 0;
                    if ( !empty( $exclude_product_ids ) || $exclude_prod_category ) {

                        $excludeProductCatDetails = $this->excludeProductCatDetails ( $product_arr, $exclude_product_ids, $exclude_prod_category, $woocommerce_discount, 'fixed_cart' );
                        $notenclude_price = !empty( $excludeProductCatDetails[ 'all_price' ] ) ? $excludeProductCatDetails[ 'all_price' ] : 0;
                        $return_arr[ 'prod_woo_id' ] = !empty( $excludeProductCatDetails[ 'prod_woo_id' ] ) ? $excludeProductCatDetails[ 'prod_woo_id' ] : [];
                        if ( isset( $excludeProductCatDetails[ 'error' ] ) ) {

                            $return_arr = ['error' => $excludeProductCatDetails[ 'error' ]];
                            return $return_arr;

                        }
                        $chckpaybleamount = !empty( $notenclude_price ) ? $notenclude_price : 0;
                        $calcualte_discount = !empty( $excludeProductCatDetails[ 'discount' ] ) ? $excludeProductCatDetails[ 'discount' ] : 0;


                    } else {
                        $objouponSelectProdId = $this->createCouponSelectProdId ( $product_arr, $product_ids, $discount_type );
                        $return_arr[ 'prod_woo_id' ] = $objouponSelectProdId[ 'select_product' ];
                        $calcualte_discount = $woocommerce_discount; //$payment_amount - $woocommerce_discount;
                        $chckpaybleamount = $payment_amount;
                    }
                    break;
                case 'percent':
                    $calcualte_discount = 0;
                    if ( !empty( $exclude_product_ids ) || $exclude_prod_category ) {
                        $excludeProductCatDetails = $this->excludeProductCatDetails ( $product_arr, $exclude_product_ids, $exclude_prod_category, $woocommerce_discount, 'percent' );
                        if ( isset( $excludeProductCatDetails[ 'error' ] ) ) {

                            $return_arr = ['error' => $excludeProductCatDetails[ 'error' ]];
                            return $return_arr;

                        }
                        $notenclude_price = $excludeProductCatDetails[ 'all_price' ];
                        $return_arr[ 'prod_woo_id' ] = $excludeProductCatDetails[ 'prod_woo_id' ];
                        if ( isset( $excludeProductCatDetails[ 'error' ] ) ) {
                            return $notenclude_price;
                        }
                        $chckpaybleamount = $notenclude_price;

                        if ( !empty( $notenclude_price ) ) {
                            $calcualte_discount = ($notenclude_price / 100) * $woocommerce_discount;
                        }

                    } else {

                        if ( !empty( $product_ids ) ) {

                            foreach ( $include_cat_base_prod_id as $rows ) {

                                if ( !in_array ( $rows, $product_ids ) ) {

                                    $product_ids [] = $rows;
                                }
                            }

                        } else {
                            foreach ( $include_cat_base_prod_id as $rows ) {
                                if ( !in_array ( $rows[ 'id' ], $product_ids ) ) {
                                    $product_ids [] = $rows[ 'id' ];

                                }

                            }

                        }

                        if(empty($product_ids)){

                            foreach ($product_arr as $row_prod_value){

                                $product_ids[] = $row_prod_value['id'];
                            }

                        }
                        $objouponSelectProdId = $this->createCouponSelectProdId ( $product_arr, $product_ids );
                        $return_arr[ 'prod_woo_id' ] = $objouponSelectProdId[ 'select_product' ];
                        $calcualte_discount = ($objouponSelectProdId[ 'price' ] / 100) * $woocommerce_discount;
                        $chckpaybleamount = $payment_amount;
                    }

                    break;
                default:
                    break;
            }
            \Log::info ( "==== AddCouponToCartInfoReqest->addCouponToCartInfoDetails PayableAmount:" . $chckpaybleamount );
            \Log::info ( "==== AddCouponToCartInfoReqest->addCouponToCartInfoDetails CalculateDiscount:" . $calcualte_discount );
            if ( !empty( floatval ( $minimum_amount ) ) && !empty( floatval ( $payment_amount ) ) ) {

                if ( floatval ( $minimum_amount ) > (floatval ( $payment_amount )) ) {

                    $return_arr = ['error' => ' Minimum amount is not reached'];
                    return $return_arr;
                }
            }

            if ( !empty( floatval ( $maximum_amount ) ) && !empty( floatval ( $payment_amount ) ) ) {

                if ( $payment_amount > $maximum_amount ) {

                    $return_arr = ['error' => 'maximum amount is exceed'];
                    return $return_arr;
                }
            }

            if ( empty( $calcualte_discount ) || empty( $return_arr[ 'prod_woo_id' ] ) ) {

                if ( !empty( $exclude_product_ids ) || !empty( $exclude_prod_category ) ) {
                      $return_arr = ['error' => 'Sorry, this coupon is not applicable to selected products.'];
                      return $return_arr;
                }

            }
            $return_arr = ['discount' => number_format ( (float)$calcualte_discount, 2, '.', '' ), 'coupon_id' => $coupon_id, 'code' => $coupon_code, 'coupn_prod_woo_id' => $return_arr[ 'prod_woo_id' ]];
            return $return_arr;
        } else {
            $return_arr = ['error' => 'Please enter Valid Coupon Code'];
            return $return_arr;
        }
    }

    public function fixedProductDiscountDetails($product_id, $discount_amount = 0)
    {
        \Log::info ( "==== AddCouponToCartInfoReqest->fixedProductDiscountDetails" );
        $ret_array = [];
        $ret_array[ 'prod_woo_id' ] = [];
        $all_discount = 0;
        $all_price = 0;
        foreach ( $product_id as $ret ) {
            $objreports = DB::table ( "reports" )->where ( "woo_id", $ret[ 'id' ] )->select ( "price" )->first ();
            for ( $x = 0; $x < $ret[ 'qty' ]; $x++ ) {
                $ret_array[ 'prod_woo_id' ][] = $ret[ 'id' ];
            }
            \Log::info ( "==== AddCouponToCartInfoReqest->fixedProductDiscountDetails", ['coupon_info_report-table_details' => json_encode ( $objreports, JSON_PRETTY_PRINT )] );
            if ( isset( $objreports->price ) ) {
                $all_discount = floatval ( $all_discount ) + floatval ( ($discount_amount * $ret[ 'qty' ]) );
                $all_price = floatval ( $all_price ) + floatval ( ($objreports->price * $ret[ 'qty' ]) );
            }
        }
        $ret_array[ 'discount' ] = $all_discount;
        $ret_array[ 'all_price' ] = $all_price;
        \Log::info ( "==== AddCouponToCartInfoReqest->fixedProductDiscountDetails", ['coupon_info_discount_detals' => json_encode ( $ret_array, JSON_PRETTY_PRINT )] );
        return $ret_array;
    }

    public function excludeProductCatDetails($product_arr, $exclude_prod_id = [], $exclude_prod_category = [], $discount_amount = 0, $discount_type = '')
    {
        \Log::info ( "==== AddCouponToCartInfoReqest->excludeProductCatDetails" );
        $allPrice = 0;
        $return_arr[ 'all_price' ] = 0;
        $return_arr[ 'prod_woo_id' ] = [];
        $all_discount = 0;
        $process_prodcut = $product_arr;
        if ( !empty( $exclude_prod_id ) ) {
            $product_arr = [];

            foreach ( $process_prodcut as $prod_res ) {

                $custProduc = $prod_res[ 'id' ];

                if ( in_array ( $custProduc, $exclude_prod_id ) ) {

                    if ( $discount_type == "fixed_cart" ) {

                        $objreports = DB::table ( "reports" )->where ( "woo_id", $custProduc )->select ( "name" )->first ();
                        $return_arr = ['error' => "Sorry, this coupon is not applicable to the products: " . $objreports->name];
                        return $return_arr;
                    }

                } else {

                    $product_arr[] = ['id' => $prod_res[ 'id' ], 'qty' => $prod_res[ 'qty' ]];

                }

            }

        }

        if ( !empty( $exclude_prod_category ) ) {

            $process_arr_val = $product_arr;
            $product_arr = [];
            $cat_exclude_flag = false;
            foreach ( $process_arr_val as $prod_res ) {

                try {
                    $productList = $this->wooCommerceService->retrieveWooData ( 'products/' . $prod_res[ 'id' ] );
                } catch (\Exception $ex) {
                    $return_arr = ['error' => $ex->getMessage ()];
                    return $return_arr;
                }
                \Log::info ( "==== AddCouponToCartInfoReqest->excludeProductCatDetails", ['chk_woocommerce_porduct' => json_encode ( $productList, JSON_PRETTY_PRINT )] );
                $product_categories = isset( $productList->categories ) ? $productList->categories : '';

                if ( !empty( $product_categories ) ) {

                    foreach ( $exclude_prod_category as $exclude_prod_category_val ) {

                        foreach ( $product_categories as $cat_res ) {

                            $custProduct[] = $cat_res->id;
                            if ( $cat_res->id == $exclude_prod_category_val ) {

                                if ( $discount_type == "fixed_cart" ) {

                                    $return_arr = ['error' => "Sorry, this coupon is not applicable to the categories: " . $cat_res->name];
                                    return $return_arr;
                                }

                                $cat_exclude_flag = true;
                            }
                        }
                        if ( $cat_exclude_flag == false ) {
                            if ( !isset( $product_id[ $prod_res[ 'id' ] ] ) ) {
                                $product_arr[] = ['id' => $prod_res[ 'id' ], 'qty' => $prod_res[ 'qty' ]];
                            }
                            $product_id[ $prod_res[ 'id' ] ] = $prod_res[ 'id' ];

                        }
                        $cat_exclude_flag = false;

                    }

                }

            }

        }


        if ( !empty( $this->fixed_product_cust_sel_id ) ) {
            $product_arr = $this->fixed_product_cust_sel_id;
        }

        if ( !empty( $product_arr ) ) {

            foreach ( $product_arr as $val ) {

                $return_arr[ 'prod_woo_id' ][] = $val[ 'id' ];

                $objreports = DB::table ( "reports" )->where ( "woo_id", $val[ 'id' ] )->select ( "price" )->first ();
                \Log::info ( "==== AddCouponToCartInfoReqest->excludeProductCatDetails", ['remain_product_arr' => json_encode ( $objreports, JSON_PRETTY_PRINT )] );
                if ( isset( $objreports->price ) ) {

                    $allPrice = $allPrice + (floatval ( $objreports->price ) * $val[ 'qty' ]);

                    if ( $discount_type == "fixed_cart" ) {

                        $all_discount = $discount_amount;
                    } else {
                        $all_discount = floatval ( $all_discount ) + floatval ( ($discount_amount * $val[ 'qty' ]) );
                    }

                }


            }

        }
        \Log::info ( "==== AddCouponToCartInfoReqest->excludeProductCatDetails", ['coupon_info_discount_detals' => json_encode ( $allPrice, JSON_PRETTY_PRINT )] );

        $return_arr[ 'discount' ] = $all_discount;
        $return_arr[ 'all_price' ] = $allPrice;

        return $return_arr;

    }

}


