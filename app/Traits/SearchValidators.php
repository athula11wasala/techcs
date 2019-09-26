<?php

namespace App\Traits;

use App\Models\Profiles;
use Illuminate\Support\Facades\Validator;
use App\Rules\CheckValidationRule;

trait SearchValidators
{

    protected function rule($method, $data)
    {

        switch ($method) {
            case 'GET':
            case 'DELETE': {


            }
            case 'POST': {


            }
            case 'PUT': {


            }

            default:
                break;
        }

    }


    protected function companyProfileValidate(array $data, $method = "POST")
    {

        $messages = [


        ];


        if ( $method == "PUT" ) {

            return Validator::make ( $data, $this->rule ( $method, $data ), $messages );

        } else {

            return Validator::make ( $data, $this->rule ( $method, $data ), $messages );
        }


    }


}








