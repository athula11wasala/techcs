<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaymentRecord extends Model
{
    protected $table = "payment_records";

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'subscr_id', 'txn_id', 'payer_id', 'item_name'
    ];

    public $timestamps = true;


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function getOrderIdAttribute($value)
    {
        $order_id = null;
        $result = DB::table ( "wp_woo_data" )->select ( "order_id" )->where ( "billing_email", $value )->first ();

        if ( !empty( $result ) ) {

            $order_id = !empty( $result->order_id ) ? $result->order_id : null;
        }
        return $order_id;

    }

    public function getPaymentStatusAttribute($value)
    {
        switch ($value) {
            case "wc-pending":
                $retrun_value = "Pending";
                break;
            case "wc-processing":
                $retrun_value = "Processing";
                break;
            case "wc-paid":
                $retrun_value = "Paid";
                break;
            case "wc-completed":
                $retrun_value = "Completed";
                break;
            case "wc-refunded":
                $retrun_value = "Refunded";
                break;
            case "wc-onhold":
                $retrun_value = "On Hold";
                break;
            default:
                $retrun_value =    $value;
        }

        return $retrun_value;

    }

}




