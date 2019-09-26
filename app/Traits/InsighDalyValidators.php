<?php

namespace App\Traits;

use App\Models\TopFive;
use Illuminate\Support\Facades\Validator;
use App\Rules\CheckValidationRule;

trait InsighDalyValidators
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
                $type = !empty( $data[ 'headline' ] ) ? $data[ 'headline' ] : 0;
                    if ( !empty( $type) ) {

                    $chkExitProfile = TopFive::where ( "headline", $data[ 'headline' ] )->first ();

                    if ( !empty( $chkExitProfile ) ) {

                        return [

                            "chkheadline" => ["required"]

                        ];

                    }
                }
                return [
                    "headline" => ["required", 'unique:insight_daily_us'],
                    'artical_image' => 'required',
                    'artical_date' => ["required"],
                    'source_orgnization' => ["required"],
                    'full_story' => ["required","max:140"],
                    'topic_type' => ["required"],
                    'artical_date' => ["required","before:tomorrow"],
                ];

            }

            case 'PUT': {

                if (!empty($data[ 'headline'])  && !empty($data[ 'id']) ) {

                    $currentProfile = TopFive::where ( "id", $data[ 'id' ] )->first ();
                    $chkexitProfile = TopFive::where ( "headline", $data[ 'headline' ] )->first ();

                    if ( !empty( $currentProfile ) & !empty( $chkexitProfile ) ) {

                        if ( ($currentProfile->id) != ($chkexitProfile->id) )
                            return [
                                "chkheadline" => ["required"]
                            ];
                    }
                }




                if ( !empty( $data[ 'artical_image' ] )   ) {

                   // if ( gettype ( $data[ 'artical_image' ] ) == 'object' ) {
                        return [
                            'id' => 'required|Integer',
                            'artical_image' => 'required',
                            'artical_date' => ["required"],
                            'source_orgnization' => ["required"],
                            'full_story' => ["required","max:140"],
                            'topic_type' => ["required"],
                            'artical_date' => ["required","before:tomorrow"],
                            'headline'=> ["required"],

                        ];

                  //  }


                }

                return [
                    'id' => 'required|Integer',
                  //  'artical_image' => 'required|image',
                    'artical_date' => ["required"],
                    'source_orgnization' => ["required"],
                    'full_story' => ["required","max:140"],
                    'topic_type' => ["required"],
                    'artical_date' => ["required","before:tomorrow"],
                    'headline'=> ["required"],

                ];

            }
            case  'ChkExistInsightDaily' : {

                $chkTopFiveData = TopFive::where ( 'id', $data[ 'id' ] )
                    ->select ( 'id' )
                    ->first ();

                if ( empty( $chkTopFiveData ) ) {
                    return [
                        "validId" => 'required',
                    ];
                }

                return [
                    "headline" => 'required|unique:insight_daily_us,headline,' . $chkTopFiveData->id,
                ];
            }
            default:
                break;
        }

    }


    protected function insightDailyValidate(array $data, $method = "POST")
    {

        $messages = [
            'id.required' => 'Please add InsightDaily Id',
            'artical_date' => 'Please add Ticker',
            'source_orgnization.required' => 'Please add Source Organization',
            'headline.required' => 'Please add HeadLine',
            'id.integer' => 'InsightDaily must be an Integer',
            'validId.required' => 'Please add proper InsightDaily Id',
            'headline.required' => 'Please add HeadLine.',
            'headline.unique' => 'there is already using this Headline.',
            'full_story.required' => 'Please add Full Story.',
            'artical_image.required' => 'Please add Artical Image.',
            'topic_type.required' => 'Please add Topic Type.',
            'articale_url.required' => 'Please add Articale Url.',
            'chkheadline.required' => 'Please add  another Headline Name.',

        ];

        if ( $method == "PUT" ) {

            $validator = Validator::make ( $data, $this->rule ( $method, $data ), $messages );

            if ( $validator->fails () ) {

                return $validator;
            } else {
                return Validator::make ( $data, $this->rule ( 'ChkExistInsightDaily', $data ), $messages );
            }
        } else {

            return Validator::make ( $data, $this->rule ( $method, $data ), $messages );
        }


    }


}








