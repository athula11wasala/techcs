<?php

namespace App\Traits;

use App\Models\Keyword;
use Illuminate\Support\Facades\Validator;
use App\Rules\CheckValidationRule;

trait KeyWordValidator
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
                    "name" => ["required", 'unique:keywords'],
                ];

            }
            case 'PUT': {

                $id = !empty( $data[ 'id' ] ) ? $data[ 'id' ] : 0;
                if ( !empty( $id ) ) {


                    $currentKeyWord = Keyword::where ( "id", $data[ 'id' ] )->first ();
                    $chkexitKeyWord = Keyword::where ( "name", $data[ 'name' ] )->first ();


                    if ( !empty( $currentKeyWord ) & !empty( $chkexitKeyWord ) ) {

                        if ( ($chkexitKeyWord->id) != ($chkexitKeyWord->id) )

                            exit;
                        return [
                            "chkName" => ["required"]

                        ];
                    }

                }

                return [
                    'id' => 'required|Integer',
                    "name" => 'required',
                ];

            }

            default:
                break;
        }

    }


    protected function keyWordValidate(array $data, $method = "POST")
    {

        $messages = [

            'id.required' => 'Please add KeyWordId',
            'name.required' => 'Please add KeyWord Name.',
            'name.unique' => 'there is already using this KeyWord.',
            'chkName.required' => 'there is already using this KeyWord.',
        ];

        if ( $method == "PUT" ) {

            return Validator::make ( $data, $this->rule ( $method, $data ), $messages );

        } else {

            return Validator::make ( $data, $this->rule ( $method, $data ), $messages );
        }

    }


}








