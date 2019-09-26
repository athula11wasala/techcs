<?php

namespace App\Traits;

use App\Models\Profiles;
use App\Models\Report;
use Illuminate\Support\Facades\Validator;
use App\Rules\CheckValidationRule;


trait InvestmentOpperunityValidator
{

    protected function rule($method, $data)
    {

        $returnValidate = [];
        switch ($method) {
            case 'GET':
            case 'DELETE':
            case 'POST': {

                return [

                    "investment_ranking" => 'required',
                    'status' => "required|integer",
                ];
            }
            case 'PUT': {

                if ( empty( $data[ 'investment_ranking' ] ) ) {

                    return [

                        "dataset_id" => 'required',
                        'status' => "required|integer",
                    ];

                }

                return [
                    "investment_ranking" => 'required',
                    "dataset_id" => 'required',
                    'status' => "required|integer",
                ];

            }
            case  'ChkExcelExtension' : {

                return [
                    "investment_ranking" => 'required|mimes:xlsx,csv',
                ];
            }
            case 'chkThreshold': {

                return [

                    'id' => "required|integer",
                    "dataset_id" => 'required|integer',
                    "low_medium" => 'required|numeric|',
                    "medium_high" => 'required|numeric|min:' . (floatval ( $data[ 'low_medium' ] ) + 0.1),
                ];


            }

            default:
                break;
        }

    }


    protected function investmentValidate(array $data, $method = "POST")
    {

        $messages = [

            'investment_ranking.required' => 'Please add Investment Opportunity Ranking.',
            'id.required' => 'Please add Id.',
            'low_medium.required' => 'Please add Low Medium.',
            'low_medium.numeric' => 'Low Medium be an Numeric',
            'low_medium.min' => 'Low Medium must be grater than zero',
            'medium_high.required' => 'Please add Medium High.',
            'medium_high.numeric' => 'Medium High be an Numeric',
            'medium_high.min' => 'Medium High must be grater than Low Medium',
            'dataset_id.required' => 'Please add DataSet Id.',
            'dataset_id.integer' => 'DataSet Id be an Integer',
            'id.integer' => 'Id be an Integer',
            'status.required' => 'Please add Status.',
            'status.integer' => 'Status be an Integer.',

        ];

        if ( $method == "PUT" ) {

            $validator = Validator::make ( $data, $this->rule ( $method, $data ), $messages );

        } else if ( $method == "chkThreshold" ) {

            $validator = Validator::make ( $data, $this->rule ( $method, $data ), $messages );

        } else {

            $validator = Validator::make ( $data, $this->rule ( $method, $data ), $messages );

        }
        if ( $validator->fails () ) {
            return $validator;
        } else {

            if ( isset( $data[ 'investment_ranking' ] ) ) {
                $image = $data[ 'investment_ranking' ];
                if ( gettype ( $image ) == 'object' ) {

                    if ( !in_array ( $image->getClientOriginalExtension (), ['xlsx', 'csv'] ) ) {
                        return Validator::make ( $data, $this->rule ( 'ChkExcelExtension', $data ), $messages );
                    }

                }


            }
            return $validator;

        }


    }

}








