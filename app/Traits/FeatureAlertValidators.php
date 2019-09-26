<?php

namespace App\Traits;

use App\Models\FeatureAlert;
use Illuminate\Support\Facades\Validator;

trait FeatureAlertValidators
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

                if (!empty($data['image'])) {

                    return [
                        'image' => 'image|mimes:jpeg,jpg,png|max:1024|dimensions:max_width=200,max_height=100',

                    ];

                }

                return [
                    "title" => ["required", "max:100", 'unique:new_features'],
                    "description" => ["required"],
                    //'image' => 'image|mimes:jpeg,jpg,png|max:1024|dimensions:max_width=300,max_height=200',
                    'link' => [''],
                    'active' => '',

                ];


            }
            case 'PUT': {

                $currentFeature = FeatureAlert::where("id", !empty($data['id']) ? $data['id'] : 0)->first();
                $chkexitFeature = FeatureAlert::where("title", !empty($data['title']) ? $data['title'] : '')->first();

                if (!empty($currentFeature) & !empty($chkexitFeature)) {

                    if (($currentFeature->id) != ($chkexitFeature->id))
                        return [
                            "chkname" => ["required"]

                        ];

                }

                if (!empty($data['image'])) {

                    return [
                        'image' => 'image|mimes:jpeg,jpg,png|max:1024|dimensions:max_width=200,max_height=100',

                    ];

                }

                return [
                    'id' => 'required|Integer',
                    "title" => ["required", "max:100"],
                    "description" => ["required"],
                    'link' => [''],
                    'active' => '',

                ];


            }


            case  'changeStatus' : {


                return [
                    'data' => 'required',
                    // 'active' => 'required',

                ];
            }


            case  'ChkExistFeature' : {

                $chkFeatureAlertata = FeatureAlert::where('id', !empty($data['id']) ? $data['id'] : 0)
                    ->select('id')
                    ->first();

                if (empty($chkFeatureAlertata)) {
                    return [
                        "validId" => 'required',
                    ];
                }

                return [
                    "title" => 'required|unique:new_features,title,' . $chkFeatureAlertata->id,
                ];
            }

        }

    }


    protected function featureAlertValidate(array $data, $method = "POST")
    {

        $messages = [
            'id.required' => 'Please add alert id',
            'active.required' => 'Please add status',
            'validId.required' => 'Please add proper FeatureId',
            'description.required' => 'Please add Description',
            'id.integer' => 'Alert id must be an Integer',
            'title.required' => 'Please add feature title.',
            'title.unique' => 'Title already exists',
            'chkname.required' => 'Please add  another Title.',
        ];


        if ($method == "POST") {

            return Validator::make($data, $this->rule($method, $data), $messages);
        }


        if ($method == "PUT") {

            $validator = Validator::make($data, $this->rule($method, $data), $messages);

        }
        if ($method == "changeStatus") {

            return Validator::make($data, $this->rule($method, $data), $messages);

        }
        if ($validator->fails()) {

            return $validator;
        } else {
            return Validator::make($data, $this->rule('ChkExistFeature', $data), $messages);
        }


    }


}








