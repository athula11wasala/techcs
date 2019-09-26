<?php

namespace App\Traits;

use App\Models\Profiles;
use App\Models\Report;
use Illuminate\Support\Facades\Validator;
use App\Rules\CheckValidationRule;


trait ActivityLogValidator
{

    protected function rule($method, $data)
    {

        $returnValidate = [];
        switch ($method) {
            case 'GET':
            case 'DELETE':
            case 'POST': {

                return [
                    "action" => 'required',
                ];
            }
            case 'ChkObject': {
                 return [
                    "object" => 'required',
                    "action" => 'required',
                    "objectId" => 'required',
                    "type" => 'required',
                ];
            }
            case 'PUT':

            default:
                break;
        }

    }


    protected function activityLogValidate(array $data, $method = "POST")
    {
        $messages = [
            'action.required' => 'Please add Audit Action.',
            'action.required' => 'Please add Object.',
            'objectId.required' => 'Please add Object ID.',
            'objectId.integer' => 'Please Object ID be an Integer.',

        ];
        $validator = Validator::make ( $data, $this->rule ( $method, $data ), $messages );

        if ( $validator->fails () ) {
            return $validator;
        } else {

            if ( isset( $data[ 'object' ] ) ) {
                $object = $data[ 'object' ];
                if ( $object == 'REPORT' ) {
                    return Validator::make ( $data, $this->rule ( 'ChkObject', $data ), $messages );
                }
            }
            return $validator;
        }
    }

}








