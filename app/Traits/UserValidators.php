<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait UserValidators
{
    protected function validateAddUser(array $data)
    {
        return Validator::make ( $data, [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'country' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'state' => 'required|string|max:50',
            'city' => 'required|string|max:255',
            'zip' => 'required', //  integer|digits_between:1,9',
            'street' => 'required|string',
//            'street2' => 'string',
            'subscription_level' => 'required|string|max:20',
            //'trail' => 'required',
//            'trail_period' => 'required|integer|digits_between:1,11',
            'role' => 'required|integer|digits_between:1,4',
            'subscription_length' => 'required|integer',
        ] );
    }

    protected function validateUpdateUser(array $data)
    {
        $date_now = date ( "Y-m-d" );
        $yestrday = date ( 'Y-m-d', strtotime ( $date_now . ' -' . 1 . 'days' ) );
        /*
        if(!empty($data['paid_subscription_start'] ) && $data['paid_subscription_end'] ){

            return Validator::make($data, [
                'first_name' => 'string|max:100',
                'last_name' => 'string|max:100',
                //'country' => 'string|max:255',
                'phone_number' => 'string|max:20',
                //'state' => 'string|max:50',
                'city' => 'string|max:255',
                'zip' => 'integer|digits_between:1,9',
                'street_address1' => 'string',
                'subscription_level' => 'string|max:20',
                'trail_period' => 'integer|digits_between:1,11',
                'role' => 'integer|digits_between:1,4',
                'paid_subscription_start'=>'date|after:'. $yestrday  ,
                'paid_subscription_end'=>'date|after:'. $data['paid_subscription_start']  ,
            ]);

        }
        */
        return Validator::make ( $data, [
            'first_name' => 'string|max:100',
            'last_name' => 'string|max:100',
            //'country' => 'string|max:255',
            'phone_number' => 'string|max:20',
            //'state' => 'string|max:50',
            'city' => 'string|max:255',
            'zip' => 'required', //  integer|digits_between:1,9',
            'street_address1' => 'string',
            'subscription_level' => 'string|max:20',
            'trail_period' => 'integer|digits_between:1,11',
            'role' => 'integer|digits_between:1,4',

        ] );

    }

}
