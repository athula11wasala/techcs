<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

trait UserProfileValidate
{

    protected function rule($method, $data)
    {

        switch ($method) {
            case 'GET':
            case 'DELETE': {
            }

            case 'PUT': {
                return [
                    "first_name" => ["required", "max:100"],
                    "last_name" => ["required", "max:100"],
                  //  "company" => ["required", "max:100"],
                    "phone_number" => ["required", "max:100"],
                    "country" => ["required", "not_in:0"],
                    "state" => ["required", "not_in:0"],
                    "zip" => ["required", "max:100"],
                    "address" => ["required"],
                    "position" => ["required", "not_in:0"],
                    "industry_role" => ["required", "not_in:0"],
                ];

            }
            case 'ChangePassword': {
                return [
                    'current_password' => 'required',
                    'password' => 'required|different:current_password',
                    'password_confirmation' => 'required|same:password'

                ];

            }
            case 'checkOldPassWord': {

                if ( Hash::check ( $data[ 'current_password' ], Auth::user ()->password ) ) {
                    return [];
                } else {

                    return [
                        "user_password" => 'required',
                    ];
                }
            }

            default:
                break;
        }

    }

    protected function pwdvalidation($val)
    {
        return false;
    }

    protected function userProfileValidate(array $data, $method = "PUT")
    {
        $messages = [

            'first_name.required' => 'Please add First Name.',
            'last_name.required' => 'Please add Last Name.',
            'company.required' => 'Please add Company.',
            'phone_number.required' => 'Please add PhoneNo.',
            'address.required' => 'Please add Address.',
            'position.required' => 'Please Select Position.',
            'industry_role.required' => 'Please Select Industry Role.',
            'country.required' => 'Please Select Country.',
            'state.required' => 'Please Select State.',
            'zip.required' => 'Please Add zip.',
            'password.required' => 'Please add New Password.',
            'password_confirmation.required' => 'Please add confirmed Password.',
            'user_password.required' => 'The Current password doesnot match with System password'
        ];

        if ( $method == "PUT" ) {

            return Validator::make ( $data, $this->rule ( $method, $data ), $messages );
        }
        if ( $method == "ChangePassword" ) {

            $validator = Validator::make ( $data, $this->rule ( $method, $data ), $messages );
            if ( $validator->passes () ) {

                return Validator::make ( $data, $this->rule ( 'checkOldPassWord', $data ), $messages );;
            } else {

                return $validator;
            }

        }


    }

}








