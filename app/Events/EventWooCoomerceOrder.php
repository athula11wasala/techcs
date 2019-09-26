<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Services\WooCommerceService;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Config;

class EventWooCoomerceOrder
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $discount_prod_woo_id = [];
    private $discount_prod_woo_id_count = [];
    private $prod_discount = 0;
    private $discount_each_item = 0;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($userInformation, $productArr = [], $stripePaymentProcessId, $userStripeRepository,$coupon_code='',$coupon_discount=0,$coupon_id= null,$discount_prod_woo_id= [],$stripe_order_id=0)
    {
        $this->wooCommerceService = new WooCommerceService( 'v3' );
        $this->userStripeRepository = $userStripeRepository;
        $this->paymenteService = new PaymentService( );
        $userInformation = $userInformation;
        $this->prod_discount = $coupon_discount;
        $this->discount_prod_woo_id = $discount_prod_woo_id;
        $this->discount_prod_woo_id_count =  !empty($discount_prod_woo_id)? count($discount_prod_woo_id) : 0;
        if( !empty($this->discount_prod_woo_id)  && !empty($coupon_id)  && !empty($coupon_discount) ){

            $this->discount_each_item =   $this->prod_discount  / count ($this->discount_prod_woo_id);

        }
        $data[ 'order' ] = [];
        $coupon_lines = [];
        $payment_details = [
            'method_id' => 'stripe',
            'method_title' => 'Stripe (Credit Card)',
            'paid' => true,

        ];
        $billing_address = [
            'first_name' => $userInformation[ 'first_name' ],
            'last_name' => $userInformation[ 'last_name' ],
            'address_1' => $userInformation[ 'street_address1' ],
            'address_2' => $userInformation[ 'street_address2' ],
            'city' => $userInformation[ 'city' ],
            'state' => $userInformation[ 'state' ],
            'postcode' => $userInformation[ 'zip' ],
            'country' => $userInformation[ 'country' ],
            'email' => $userInformation[ 'email' ],
            'phone' => $userInformation[ 'phone_number' ],
        ];

        if(!empty($coupon_discount) && !empty($coupon_id)){

            $coupon_lines[] = [

                'code'=>  'testdev', //$coupon_code,
                'discount'=>   $coupon_discount, //floatval ( $coupon_discount),
                'discount_tax' => '0',
                'meta_data' => [
                    [
                        'key' => 'coupon_data',
                        'value' => [
                            'id' => $coupon_id,
                            'code' =>   $coupon_code,
                            'amount' => $coupon_discount,

                        ]
                    ]
                ]
            ];

        }

        if ( !empty( $productArr ) ) {

            foreach ( $productArr as $rows ) {

                if ( !empty( $rows[ 'bundle' ] ) ) {

                    foreach ( $rows[ 'bundle' ] as $bundle_result ) {


                        if ( !isset( $bundle_result[ 'id' ] ) || !isset( $bundle_result[ 'qty' ] ) ) {
                            return false;
                        }

                        $line_items [] =
                            [
                                'product_id' => $bundle_result[ 'id' ],
                                'quantity' => $bundle_result[ 'qty' ]
                            ];
                    }

                } else {

                    if ( !isset( $rows[ 'id' ] ) || !isset( $rows[ 'qty' ] ) ) {
                        return false;
                    }

                    $prod_total_info =      $this->discountCalcProd($rows[ 'id' ], $rows[ 'qty' ]);

                    $line_items [] =
                        [

                            'product_id' => $rows[ 'id' ],
                            'quantity' => $rows[ 'qty' ],
                            "subtotal"=>  $prod_total_info['subtotal'],
                            "total"=>  $prod_total_info['total'],

                        ];


                }

            }
        }
        $shipping_lines = [
            [
                'method_id' => 'flat_rate',
                'method_title' => 'Flat Rate',
                'total' => 0
            ]
        ];

        $data[ 'order' ][ 'payment_details' ] = $payment_details;
        $data[ 'order' ][ 'billing_address' ] = $billing_address;
        $data[ 'order' ][ 'line_items' ] = $line_items;
        $data[ 'order' ][ 'shipping_lines' ] = $shipping_lines;
        //$data[ 'order' ][ 'coupon_lines' ] = $coupon_lines ;
        //$data[ 'order' ][ 'status' ] =  "completed"; //"on-hold";


        \Log::info(  "==== orderDAta"   , ['remain_product_arr' => json_encode($data, JSON_PRETTY_PRINT)]);
        $order = $this->wooCommerceService->storeWooData ( 'orders', $data );
        if ( empty( $order ) ) {
            $this->userStripeRepository->updateUserStripe ( $stripePaymentProcessId, false );
            return false;
        } else {


            $data = [
                'order' => [
                    'status' => 'completed',

                ],

            ];
            $this->paymenteService->setChargeDescription($stripe_order_id,"Order ". $order->order->id. ": ", Config::get('custom_config.Payment_TYPE')[1]);

            $orderId = !empty($order->order->id)? $order->order->id : 0;
            $this->wooCommerceService->updateWooData ( "orders/" . $orderId, $data );
            $this->userStripeRepository->updateUserStripe ( $stripePaymentProcessId, true );
            return true;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel( 'channel-name' );
    }

    public  function  discountCalcProd($prod_item,$qty) {

        \Log::info(  "==== EventWoocommerceOrder->discountCalcProd"   , ['argumets' => json_encode(['prodd'=>$prod_item, 'qty'=>$qty], JSON_PRETTY_PRINT)]);
        $product_db_info =  DB::table("reports")->select("id","price")->where("woo_id",$prod_item)->first();
        $product_price =  !empty($product_db_info->price) ? $product_db_info->price : 0 ;
        $ret_array['subtotal'] = $product_price * $qty;
        $ret_array['total'] =$product_price * $qty;

        if( !empty($this->discount_prod_woo_id)){

            if ( in_array ($prod_item, $this->discount_prod_woo_id  ) ) {

                if( !empty($this->prod_discount)  ){

                    $this->discount_each_item =   $this->prod_discount  / $this->discount_prod_woo_id_count;
                    $ret_array['total'] = floatval ($ret_array['subtotal']) - floatval ($this->discount_each_item  * $qty)   ;
                    \Log::info(  "==== EventWoocommerceOrder->discountCalcProd"   , ['obj' => json_encode(['discount_each_item'=>  $this->discount_each_item,'total'=>$ret_array], JSON_PRETTY_PRINT)]);

                    $this->discount_prod_woo_id_count = $this->discount_prod_woo_id_count -  $qty;
                    $this->prod_discount  =  $this->prod_discount  - ( $this->discount_each_item * $qty);
                }

            }
        }

        return $ret_array;

    }

}

