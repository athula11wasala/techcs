<?php

namespace App\Traits;

use App\Models\Profiles;
use App\Models\Report;
use Illuminate\Support\Facades\Validator;
use App\Rules\CheckValidationRule;


trait ReportValidator
{

    protected function rule($method, $data)
    {

        $returnValidate = [];
        switch ($method) {
            case 'GET':
            case 'DELETE': {

                return [
                    'id' => 'required|Integer',
                ];
            }
            case 'POST': {

                if ( !isset( $data[ 'report_type' ] ) ) {

                    return [

                        "report_type" => ["required"]
                    ];

                }


                if ( $data[ 'report_type' ] == "simple" ) {

                    return [

                        "simple_digital_woo_id" => ["required"],
                        //  "simple_hard_copy_woo_id" => ["required"],
                        "simple_digital_price" => ["required"],
                        //  "simple_hardcopy_price" => ["required"],
                        "simple_report_name" => ["required", "max:100", 'unique:reports,name'],
                        "simple_report_category" => ["required"],
                        "simple_report_segment" => "required|integer",
                        // "simple_state_name" => ["required"],
                        // "simple_state_id" => "required|integer",
                        "simple_marketing_description" => ["required"],
                        "simple_cover_image" => 'required|image|mimes:jpeg,jpg|max:1024|dimensions:max_width=353,max_height=454',
                        "simple_purchase_url" => ["required"],
                        "simple_exec_summary_pdf" => ['required', 'mimes:pdf', 'max:20480'],
                        "simple_full_pdf" => ['required', 'mimes:pdf', 'max:5120'],
                        "simple_enterprise_pdf" => ['required', 'mimes:pdf', 'max:5120'],
                        "simple_publish" => ["required", "integer"],


                    ];


                } else {


                    return [

                        "simple_digital_woo_id" => ["required"],
                        //  "simple_hard_copy_woo_id" => ["required"],
                        "simple_digital_price" => ["required"],
                        //  "simple_hardcopy_price" => ["required"],
                        "simple_report_name" => ["required", "max:100", 'unique:reports,name'],
                        "simple_report_category" => ["required"],
                        "simple_report_segment" => "required|integer",
                        // "simple_state_name" => ["required"],
                        // "simple_state_id" => "required|integer",
                        "simple_marketing_description" => ["required"],
                        "simple_cover_image" => 'required|image|mimes:jpeg,jpg|max:1024|dimensions:max_width=353,max_height=454',
                        "simple_purchase_url" => ["required"],
                        "simple_exec_summary_pdf" => ['required', 'mimes:pdf', 'max:20480'],
                        "simple_full_pdf" => ['required', 'mimes:pdf', 'max:5120'],
                        "simple_enterprise_pdf" => ['required', 'mimes:pdf', 'max:5120'],
                        //    "simple_chart_image_file" => "required|mimes:zip",
                        //  "simple_chart_keyword" =>  'required',
                        "simple_publish" => ["required", "integer"],
                        "interactive_summary" => ["required"],
                        "interactive_key_findings_text" => ["required"],
                        "interactive_key_findings_list" => ["required"],
                        "interactive_canaclip_url" => ["required"],
                        "credit_author" => ["required"],
                        "credit_author_research_analyst" => ["required"],
                        "credit_author_marketing_pr" => ["required"],
                        "credit_author_photo" => 'required|image|mimes:jpeg,jpg|max:5120',

                    ];


                }


            }
            case 'PUT': {

                if ( !isset( $data[ 'id' ] ) ) {

                    return [

                        'id' => 'required|Integer',
                    ];


                }


                if ( !isset( $data[ 'report_type' ] ) ) {

                    return [

                        "report_type" => ["required"]
                    ];

                }


                $currentReport = Report::where ( "id", !empty( $data[ 'id' ] ) ? $data[ 'id' ] : '' )->first ();
                $chkexitReport = Report::where ( "name", !empty( $data[ 'simple_report_name' ] ) ? $data[ 'simple_report_name' ] : '' )->first ();

                if ( !empty( $currentReport ) & !empty( $chkexitReport ) ) {

                    if ( ($currentReport->id) != ($chkexitReport->id) )
                        return [
                            "chkname" => ["required"]

                        ];

                }


                if ( !empty( $data[ 'simple_cover_image' ] )
                    && !empty( $data[ 'simple_exec_summary_pdf' ] )
                    && !empty( $data[ 'simple_full_pdf' ] )
                    && !empty( $data[ 'simple_enterprise_pdf' ]
                    )
                ) {


                    if ( $data[ 'report_type' ] == "simple" ) {


                        return [

                            'id' => 'required|Integer',
                            "simple_report_name" => ["required", "max:100"],
                            "simple_report_category" => ["required"],
                            "simple_report_segment" => "required|integer",
                            //  "simple_state_name" => ["required"],
                            // "simple_state_id" => "required|integer",
                            "simple_marketing_description" => ["required"],
                            "simple_cover_image" => 'required|image|mimes:jpeg,jpg|max:1024|dimensions:max_width=353,max_height=454',
                            "simple_purchase_url" => ["required"],
                            "simple_exec_summary_pdf" => ['required', 'mimes:pdf', 'max:20480'],
                            "simple_full_pdf" => ['required', 'mimes:pdf', 'max:5120'],
                            "simple_enterprise_pdf" => ['required', 'mimes:pdf', 'max:5120'],

                        ];


                    } else {

                        if ( !empty( $data[ 'credit_author_photo' ] )) {

                            return [
                                "credit_author_photo" => 'required|image|mimes:jpeg,jpg|max:5120',
                            ];

                        }

                        return [

                            'id' => 'required|Integer',
                            "simple_report_name" => ["required", "max:100"],
                            "simple_report_category" => ["required"],
                            "simple_report_segment" => "required|integer",
                            //  "simple_state_name" => ["required"],
                            // "simple_state_id" => "required|integer",
                            "simple_marketing_description" => ["required"],
                            "simple_cover_image" => 'required|image|mimes:jpeg,jpg|max:1024|dimensions:max_width=353,max_height=454',
                            "simple_purchase_url" => ["required"],
                            "simple_exec_summary_pdf" => ['required', 'mimes:pdf', 'max:20480'],
                            "simple_full_pdf" => ['required', 'mimes:pdf', 'max:5120'],
                            "simple_enterprise_pdf" => ['required', 'mimes:pdf', 'max:5120'],
                            //  "simple_publish" => ["required","integer"],
                            "interactive_summary" => ["required"],
                            "interactive_key_findings_text" => ["required"],
                            "interactive_key_findings_list" => ["required"],
                            "interactive_canaclip_url" => ["required"],
                            "credit_author" => ["required"],
                            "credit_author_research_analyst" => ["required"],
                            "credit_author_marketing_pr" => ["required"],
                          

                        ];


                    }


                }

                if ( !empty( $data[ 'simple_cover_image' ] )

                ) {

                    return [

                        "simple_cover_image" => 'required|image|mimes:jpeg,jpg|max:1024|dimensions:max_width=353,max_height=454',

                    ];

                }


                if ( !empty( $data[ 'simple_exec_summary_pdf' ] )

                ) {

                    return [

                        "simple_exec_summary_pdf" => ['required', 'mimes:pdf', 'max:5120'],

                    ];

                }

                if ( !empty( $data[ 'simple_full_pdf' ] )

                ) {

                    return [

                        "simple_full_pdf" => ['required', 'mimes:pdf', 'max:5120'],

                    ];

                }

                if ( !empty( $data[ 'simple_enterprise_pdf' ] )

                ) {

                    return [

                        "simple_enterprise_pdf" => ['required', 'mimes:pdf', 'max:5120'],

                    ];

                }

                if ( !empty( $data[ 'credit_author_photo' ] )

                ) {

                    return [

                        "credit_author_photo" => 'required|image|mimes:jpeg,jpg|max:5120',

                    ];

                }

                if ( $data[ 'report_type' ] == "simple" ) {


                    return [

                        'id' => 'required|Integer',
                        "simple_report_name" => ["required", "max:100"],
                        "simple_report_category" => ["required"],
                        "simple_report_segment" => "required|integer",
                        //  "simple_state_name" => ["required"],
                        // "simple_state_id" => "required|integer",
                        "simple_marketing_description" => ["required"],
                        "simple_purchase_url" => ["required"],

                    ];


                } else {


                    return [

                        'id' => 'required|Integer',
                        "simple_report_name" => ["required", "max:100"],
                        "simple_report_category" => ["required"],
                        "simple_report_segment" => "required|integer",
                        //  "simple_state_name" => ["required"],
                        // "simple_state_id" => "required|integer",
                        "simple_marketing_description" => ["required"],
                        "simple_purchase_url" => ["required"],
                        "interactive_summary" => ["required"],
                        "interactive_key_findings_text" => ["required"],
                        "interactive_key_findings_list" => ["required"],
                        "interactive_canaclip_url" => ["required"],
                        "credit_author" => ["required"],
                        "credit_author_research_analyst" => ["required"],
                        "credit_author_marketing_pr" => ["required"],


                    ];


                }


            }
            case  'ChkChartKeyowordExtension' : {

                $excel = !empty($data[ 'simple_chart_keyword' ]) ?  $data[ 'simple_chart_keyword' ]:'';


                if ( gettype ( $excel ) == 'object' ) {



                    return [
                        "simple_chart_keyword" => 'required|mimes:xlsx,csv',
                    ];
                }
                }




            case  'ChkChartKeyowordImgWithExcel' : {

                $image =  !empty($data[ 'simple_chart_keyword' ]) ?  $data[ 'simple_chart_keyword' ]:'';


                if ( gettype ( $image ) == 'object' ) {

                    return [
                        "simple_chart_image_file" => "required|mimes:zip",
                        "simple_chart_keyword" => 'required',
                    ];
                }
                return [
                    "simple_chart_image_file" => "required|mimes:zip",

                ];

                }





            default:
                break;
        }

    }


    protected function reportValidate(array $data, $method = "POST")
    {

        $messages = [


            'simple_report_name.unique' => 'there is already using this reportName.',
            'id.required' => 'Please add ReportId',
            'simple_digital_woo_id.required' => 'Please add Digital wooId',
            'hard_copy_woo_id.required' => 'Please add hard copy wooId',
            'simple_digital_price.required' => 'Please add digital Price',
            'simple_hardcopy_price.required' => 'Please add hard copy price',
            'simple_report_name.required' => 'Please add Report Name',
            'simple_report_category.required' => 'Please add Report Categroy',
            'simple_report_segment.required' => 'Please add Report Segment',
            'simple_state_id.required' => 'Please add State Id',
            'simple_state_name.required' => 'Please add State Name',
            'simple_marketing_description.required' => 'Please add Market Description',
            'simple_purchase_url' => 'Please add Purchase Url',
            'simple_cover_image.required' => 'Please add Cover Image',
            'simple_exec_summary_pdf.required' => 'Please add Exec  summary',
            'simple_full_pdf.required' => 'Please add Full Pdf',
            'simple_enterprise_pdf.required' => 'Please add Enterprise Pdf',
            'simple_chart_image_file.required' => 'Please add Chart Image File ',
            'simple_chart_keyword.required' => 'Please add chart keyword',
            'simple_publish.required' => 'Please add Publish',
            'interactive_summary.required' => 'Please add Interactive Report Summary',
            "interactive_key_findings_text.required" => 'Please add Interactive Key Finding Text',
            'interactive_key_findings_list.required' => 'Please add Interactive Key Finding List',
            'interactive_canaclip_url.required' => 'Please add Cannaclip Url',
            'credit_author.required' => 'Please add Author',
            'credit_author_research_analyst.required' => 'Please add Research & analyst',
            'credit_author_marketing_pr.required' => 'Please add Credit Marketing Pr',
            'credit_author_photo.required' => 'Please add Author Photo.',
            'chkname.required' => 'Please add  another Report Name.',
            'id.required' => 'Please add ReportId.',
            'id.integer' => 'ReportId must be an Integer',
            'report_type.required' => 'Please add ReportId',

        ];

        if ( $method == "PUT" ) {

            $validator = Validator::make ( $data, $this->rule ( $method, $data ), $messages );

        } else {

            $validator = Validator::make ( $data, $this->rule ( $method, $data ), $messages );

        }
        if ( $validator->fails () ) {
            return $validator;
        } else {

            if ( isset( $data[ 'simple_chart_image_file' ] ) ) {

                $validator = Validator::make ( $data, $this->rule ( 'ChkChartKeyowordImgWithExcel', $data ), $messages );
                if ( $validator->fails () ) {
                    return $validator;
                }


                $image =  !empty($data[ 'simple_chart_keyword' ]) ?  $data[ 'simple_chart_keyword' ]:'';


                if ( gettype ( $image ) == 'object' ) {

                    if ( !in_array ( $image->getClientOriginalExtension (), ['xlsx', 'csv'] ) ) {
                        return Validator::make ( $data, $this->rule ( 'ChkChartKeyowordExtension', $data ), $messages );
                    }

                } else {

                    return Validator::make ( $data, $this->rule ( 'ChkChartKeyowordExtension', $data ), $messages );
                }

            }

            return $validator;

        }


    }

}








