<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait ModuleSubscriptionTrackerValidators {

    /**
     * @param $method
     * @param $data
     * @return array
     */
    protected function rule($method, $data) {

        switch ($method) {
            case 'GET':
            case 'DELETE': {

                return [
                    'id' => 'required|Integer',
                ];
            }
            case 'POST': {
                return [
                    "plan_id" => ["required"],
                    "company" => ["required"]
                ];
            }
            case 'PUT': {
                return [
                    'company' => ["required"]
                ];
            }
        }
    }


    /**
     * @param array $data
     * @param string $method
     * @return mixed
     */
    protected function moduleSubscriptionValidate(array $data, $method = "") {

        $messages = [
            'plan_id.required' => 'Please add plan',
            'company.required' => 'Please add company',
        ];

        return Validator::make($data, $this->rule($method, $data), $messages);


    }


}








