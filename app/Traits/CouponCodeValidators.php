<?php

namespace App\Traits;

use App\Models\Profiles;
use Illuminate\Support\Facades\Validator;
use App\Rules\CheckValidationRule;

trait CouponCodeValidators
{

    protected function rule($method, $data)
    {
        switch ($method) {
            case 'GET':
            case 'DELETE': {

            return [
            ];
            }
            case 'POST': {
                return [

                ];

            }
            case  'ChkExistcoupon' : {

                return [
                  ''
                ];
            }
            default:
                break;
        }

    }


    protected function couponCodeValidate(array $data, $method = "POST")
    {

        $messages = [

            'id.required' => 'Please add CoupopnId',
        ];

        if ($method == "PUT") {

            $validator = Validator::make($data, $this->rule($method, $data), $messages);

            if ($validator->fails()) {

                return $validator;
            } else {
                return Validator::make($data, $this->rule('ChkExistcoupon', $data), $messages);
            }
        } else {

            return Validator::make($data, $this->rule($method, $data), $messages);
        }


    }


}








