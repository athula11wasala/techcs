<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait CsvDatasetValidators
{

    protected function rule($method, $data)
    {

        switch ($method) {
            case 'GET':

            case 'POST': {
                return [
                    'type' => ["required", "not_in:0"],
                    "quater" => ["required", "max:100"],
                    "fromDate" => ["required", "max:100", "date"],
                    "toDate" => ["required", "max:100", "date"],
                    'datacsv' => 'required',
                ];

            }

            default:
                break;
        }
    }


    protected function dataSetValidate(array $data, $method = "POST")
    {
        $messages = [
            'type.required' => 'Please select Dataset Type',
            'quater.required' => 'Plese add quater',
            'fromDate.required' => 'Please add From date',
            'toDate.required' => 'Please add To Date.',
            'datacsv' => 'Please add Csv File.',

        ];


        $validator = Validator::make ( $data, $this->rule ( $method, $data ), $messages );

        if ( $validator->fails () ) {

            return $validator;
        } else {

            return Validator::make (

                [
                    'datacsv' => $data[ 'datacsv' ],
                    'extension' => strtolower ( $data[ 'datacsv' ]->getClientOriginalExtension () ),
                ],
                [
                    'datacsv' => 'required',
                    'extension' => 'required|in:csv,xlsx,xls',
                ] );


        }


    }


}








