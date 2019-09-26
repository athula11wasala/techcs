<?php

namespace App\Traits;

use App\Models\Profiles;
use Illuminate\Support\Facades\Validator;
use App\Rules\CheckValidationRule;

trait UserAcknowledgmentValidators
{

    protected function rule($method, $data)
    {

        switch ($method) {
            case 'GET':
            case 'DELETE': {

                return [
                    'id' => 'required|Integer',
                ];
            }
            case 'POST': {


                return [
                    'id' => 'required|Integer',

                ];


            }
            case 'PUT': {


            }

            default:
                break;
        }

    }


    protected function acknowlwdgmentValidate(array $data, $method = "POST")
    {

        $messages = [

            'id.required' => 'Please add FeatureId',


        ];

        return Validator::make ( $data, $this->rule ( $method, $data ), $messages );


    }


}








