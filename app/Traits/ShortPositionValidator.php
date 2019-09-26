<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use App\Rules\CheckValidationRule;


trait ShortPositionValidator
{

    protected function rule($action, $data)
    {

        switch ($action) {
            case 'GET':
            case 'DELETE': {

                
            }
            case 'POST': {
                return [
                    'companyCode' => 'required|string|max:5',

                ];
            }
            case 'PUT': {


            }
            case 'CANCEL_PLAN': {
                return [
                    "plan_id" => ["required"],
                    "company" => ["required"]
                ];
            }

            case 'CHANGE_COMPANIES': {
                return [
                    "price_plan_id" => ["required"],
                    "company" => ["required"]
                ];
            }

            default:
                break;
        }

    }


    protected function companyCodeValidate(array $data, $action = "POST")
    {

        $messages = [

            'companyCode.required' => 'Please add company code',


        ];

        return Validator::make ( $data, $this->rule ( $action, $data ), $messages );


    }

    /**
     * @param array $data
     * @param string $action
     * @return mixed
     */
    protected function cancelPlanValidate(array $data, $action = "CANCEL_PLAN") {

        $messages = [
            'plan_id.required' => 'Please add plan',
            'company.required' => 'Please add company',
        ];

        return Validator::make($data, $this->rule($action, $data), $messages);


    }

    /**
     * @param array $data
     * @param string $action
     * @return mixed
     */
    protected function changeCompaniesValidate(array $data, $action = "CHANGE_COMPANIES") {

        $messages = [
            'price_plan_id.required' => 'Please add plan',
            'company.required' => 'Please add company',
        ];

        return Validator::make($data, $this->rule($action, $data), $messages);


    }


}








