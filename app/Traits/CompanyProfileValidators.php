<?php

namespace App\Traits;

use App\Models\Profiles;
use Illuminate\Support\Facades\Validator;
use App\Rules\CheckValidationRule;

trait CompanyProfileValidators
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
                $type = !empty($data['profile_type']) ? $data['profile_type'] : 0;
                if ($type == 1) {

                    $chkExitProfile = Profiles::where("name", $data['name'])->first();
                    if (!empty($chkExitProfile)) {

                        return [

                            "chkname" => ["required"]

                        ];

                    }
                }

                if ($type == 2) {

                    $chkExitProfile = Profiles::where("name", $data['name'])->first();
                    if (!empty($chkExitProfile)) {

                        return [

                            "chkcountry" => ["required"]

                        ];

                    }
                }

                if (!empty($data['company_logo'])) {
                    return [
                        "name" => ["required", "max:100", 'unique:profiles'],
//                        "description" => ["required"],
                        'profile_cover' => 'required|image|mimes:jpeg,jpg|max:1024|dimensions:max_width=353,max_height=454',
                        'profile_document' => ['required', 'mimes:pdf', 'max:5120'],
                        'company_logo' => 'image|mimes:png|max:1024|dimensions:max_width=250,max_height=250',
                        'profile_type' => ["required", "not_in:0"],
                        'ticker' => [new  CheckValidationRule()],

                    ];

                }
                return [
                    "name" => ["required", "max:100", 'unique:profiles'],
//                    "description" => ["required"],
                    'profile_cover' => 'required|image|mimes:jpeg,jpg|max:1024|dimensions:max_width=353,max_height=454',
                    'profile_document' => ['required', 'mimes:pdf', 'max:5120'],
                    'profile_type' => ["required", "not_in:0"],
                    'ticker' => [new  CheckValidationRule()],

                ];


            }
            case 'PUT': {

                $type = !empty($data['profile_type']) ? $data['profile_type'] : 0;
                if ($type == 1) {

                    $currentProfile = Profiles::where("id", $data['id'])->first();
                    $chkexitProfile = Profiles::where("name", $data['name'])->first();

                    if (!empty($currentProfile) & !empty($chkexitProfile)) {

                        if (($currentProfile->id) != ($chkexitProfile->id))
                            return [
                                "chkname" => ["required"]

                            ];

                    }
                    //
                }

                if ($type == 2) {

                    $currentProfile = Profiles::where("id", $data['id'])->first();
                    $chkexitProfile = Profiles::where("name", $data['name'])->first();

                    if (!empty($currentProfile) & !empty($chkexitProfile)) {

                        if (($currentProfile->id) != ($chkexitProfile->id))
                            return [
                                "chkcountry" => ["required"]

                            ];

                    }
                    //
                }


                if (!empty($data['profile_cover']) && !empty($data['profile_document'])) {
                    return [
                        'id' => 'required|Integer',
                        "name" => 'required',
//                        "description" => ["required"],
                        'profile_cover' => 'required|image|mimes:jpeg,jpg|max:1024|dimensions:max_width=353,max_height=454',
                        'company_logo' => 'image|mimes:png|max:1024|dimensions:max_width=250,max_height=250',
                        'profile_document' => ['required', 'mimes:pdf', 'max:5120'],
                        'profile_type' => ["required", "not_in:0"],
                        'ticker' => [new  CheckValidationRule()],
                    ];

                }

                if (empty($data['profile_cover']) && !empty($data['profile_document'])) {
                    return [
                        'id' => 'required|Integer',
                        "name" => 'required',
//                        "description" => ["required"],
                        'profile_document' => ['required', 'mimes:pdf', 'max:10000'],
                        'company_logo' => 'image|mimes:png|max:1024|dimensions:max_width=250,max_height=250',
                        'profile_type' => ["required", "not_in:0"],
                        'ticker' => [new  CheckValidationRule()],
                    ];

                }

                if (!empty($data['profile_cover']) && empty($data['profile_document'])) {

                    return [
                        'id' => 'required|Integer',
                        "name" => 'required',
//                        "description" => ["required"],
                        'profile_cover' => 'required|image|mimes:jpeg,jpg|max:1024|dimensions:max_width=353,min_height=454',
                        'company_logo' => 'image|mimes:png|max:1024',
                        'profile_type' => ["required", "not_in:0"],
                        'ticker' => [new  CheckValidationRule()],
                    ];

                }

                return [
                    'id' => 'required|Integer',
                    "name" => 'required',
//                    "description" => ["required"],
                    'profile_type' => ["required", "not_in:0"],
                    'ticker' => [new  CheckValidationRule()],
                ];

            }
            case  'ChkExistcompany' : {

                $chkCompanyData = Profiles::where('id', $data['id'])
                    ->select('id')
                    ->first();

                if (empty($chkCompanyData)) {
                    return [
                        "validId" => 'required',
                    ];
                }

                return [
                    "name" => 'required|unique:profiles,name,' . $chkCompanyData->id,
                ];
            }
            default:
                break;
        }

    }


    protected function companyProfileValidate(array $data, $method = "POST")
    {

        $messages = [

            'id.required' => 'Please add CompanyId',
            'ticker' => 'Please add Ticker',
            'ticker.required' => 'Please add Ticker Symbol',
            'description.required' => 'Please add Description',
            'id.integer' => 'CompanyId must be an Integer',
            'validId.required' => 'Please add proper CompanyId',
            'name.required' => 'Please add Company Name.',
            'name.unique' => 'there is already using this Company Name.',
            'profile_cover.required' => 'Please add Profile Cover.',
            'company_logo.required' => 'Please add Company Logo.',
            'profile_document.required' => 'Please add Profile Document.',
            'profile_type.required' => 'Please add Profile Type.',
            'chkcountry.required' => 'Please add  another Country.',
            'chkname.required' => 'Please add  another Profile Name.',
            // 'chkcountry.required' => 'there is already using this Country Name.',
            // 'country.required' => 'Please select Country.',

        ];


        if ($method == "PUT") {

            $validator = Validator::make($data, $this->rule($method, $data), $messages);

            if ($validator->fails()) {

                return $validator;
            } else {
                return Validator::make($data, $this->rule('ChkExistcompany', $data), $messages);
            }
        } else {

            return Validator::make($data, $this->rule($method, $data), $messages);
        }


    }


}








