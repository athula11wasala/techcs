<?php

namespace App\Repositories;

use App\Equio\Exceptions\EquioException;
use App\Models\Report;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use App\Models\Chart;
use App\Models\Keyword;
use App\Models\ChartKeywords;
use App\Models\InteractiveReport;
use App\Equio\Helper;
use Doctrine\DBAL\Driver\PDOException;
use Illuminate\Container\Container as App;
use Illuminate\Support\Facades\Config;

class ReportRepository extends Repository
{
    protected $perPage;
    protected $sort;
    protected $sortColumn;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\Report';
    }

    public function getAllReports($request)
    {
        return $this->model->select ( 'reports.*' )
            ->where ( 'reports.available', "=", 1 )
            ->groupBy ( 'reports.name' )
            ->orderBy ( 'woo_id', 'DESC' )
            ->get ();
    }

    public function getReportUrlById($id)
    {
        return $this->model->select ( 'reports.*' )
            //->where('reports.available', "=", 1)
            ->where ( 'reports.id', "=", $id )
            ->groupBy ( 'reports.name' )
            ->first ();
    }

    /**
     * Get all available reports
     * @param array $where
     * @param bool $group
     * @return mixed
     * @throws EquioException
     */
    public function getReports($where = array(), $group = true)
    {

        try {
            $model = $this->model->select ( 'reports.*' )->whereIn ( 'reports.available', [1, 0] );
            \Log::info ( "===== where ", ['w' => $where] );
            foreach ( $where as $field => $value ) {
                if ( $value instanceof \Closure ) {
                    $model = $model->where ( $value );
                } else {
                    list( $field, $operator, $search ) = $value;
                    $model = $model->where ( $field, $operator, $search );
                }
            }

            if ( $group ) {
                $model = $model->groupBy ( 'reports.name' );
            }
            return $model->get ();
        } catch (PDOException $exception) {
            throw new EquioException(
                "Error occured while fetching reports - " . $exception->getMessage (),
                $exception->getCode ()
            );
        } catch (\Exception $exception) {
            throw new EquioException(
                "Error occured while fetching reports - " . $exception->getMessage (),
                $exception->getCode ()
            );
        }
    }

    /**
     * Get all available reports
     * @param array $where
     * @param bool $group
     * @return mixed
     * @throws EquioException
     */
    public function getAvailbleReports($segment = null, $group = true, $wooCommerceIds = [], $subscription_level = '')
    {
        try {
            $model = $this->model->select ( 'reports.*' );
          //  $model = $model->where ( "reports.segment", '=', $segment );
            if ( !empty( $wooCommerceIds ) ) {

                if ( $subscription_level == Config::get ( 'custom_config.PACKAGE_ESSENTIAL' ) ) {
                    $model = $model->orWhere ( function ($query) use ($wooCommerceIds, $segment) {

                        $query->whereIn ( 'reports.woo_id', $wooCommerceIds )
                          //  ->where ( "reports.available", '=', 1 )
                            ->where ( "reports.segment", '=', $segment );
                    } );
                }
            }

            if ( !empty( $subscription_level ) ) {

                if ( $subscription_level == Config::get ( 'custom_config.PACKAGE_ENTERPRISE' ) || $subscription_level == Config::get ( 'custom_config.PACKAGE_PREMIUMPLUS' )
                    || $subscription_level == Config::get ( 'custom_config.PACKAGE_PREMIUM' ) ) {

                    $model = $model->where ( "reports.segment", '=', $segment );
                    $model = $model->where ( "reports.available", '=', 1 )->where ( "reports.exempt", 1 );

                    if ( !empty( $wooCommerceIds ) ) {
                        $model = $model->orWhere ( function ($query) use ($wooCommerceIds,$segment) {
                            $query->whereIn ( "reports.woo_id", $wooCommerceIds )
                                ->where ( "reports.segment", '=', $segment );
                               // ->where ( "reports.available", '=', 1 );

                        } );

                    }

                }

                if ( $subscription_level == Config::get ( 'custom_config.PACKAGE_ESSENTIAL' ) ) {

                    $model = $model->orWhere ( function ($query) use ($segment) {
                        $query->where ( 'reports.price', 0 )
                            ->where ( "reports.available", '=', 1 )
                            ->where ( "reports.segment", '=', $segment );
                    } );
                }
            }

            if ( $group ) {
                $model = $model->groupBy ( 'reports.name' );
            }
            \Log::info ( "===== database ", ['w' => $model->toSql ()] );
            return $model->get ();
        } catch (PDOException $exception) {
            throw new EquioException(
                "Error occured while fetching reports - " . $exception->getMessage (),
                $exception->getCode ()
            );
        } catch (\Exception $exception) {
            throw new EquioException(
                "Error occured while fetching reports - " . $exception->getMessage (),
                $exception->getCode ()
            );
        }
    }

    public function getAllReportsName()
    {
        return $this->model->select ( 'reports.name' )->where ( 'reports.available', "=", 1 )->groupBy ( 'reports.name' )->orderBy ( 'woo_id', 'DESC' )->get ();
    }

    public function getPurchasedReports($request)
    {
        $result = $this->model->select ( 'reports.*' );
        //$result = $result->where('reports.available', "=", 1);
        $result = $result->whereIn ( 'reports.woo_id', $request )
            ->orderBy ( 'woo_id', 'DESC' )->get ();
        return $result;
    }

    public function getPurchasedReportName($request)
    {
        $result = [];
        $resultObj = $this->model->select ( 'reports.name' )
            //->where('reports.available', "=", 1)
            ->whereIn ( 'reports.woo_id', $request )
            ->groupBy ( 'reports.name' )->orderBy ( 'woo_id', 'DESC' )->get ();

        if ( !empty( $resultObj ) ) {

            foreach ( $resultObj as $arr ) {

                $result [] = $arr->name;
            }
        }
        return $result;
    }

    public function getPurchasedReportEssential($request)
    {
        $result = [];
        $resultObj = $this->model->select ( 'reports.id' )
            ->whereIn ( 'reports.woo_id', $request )
            ->orderBy ( 'woo_id', 'DESC' )->get ();

        if ( !empty( $resultObj ) ) {

            foreach ( $resultObj as $arr ) {

                $result [] = $arr->id;
            }
        }
        return $result;

    }

    public function getPriceZeroReportName($id)
    {
        return $this->model->select ( 'reports.name' )
            ->where ( 'reports.available', "=", 1 )
            ->where ( 'reports.id', $id )
            ->where ( 'reports.price', 0 )
            ->first ();
    }

    public function getReportName($id)
    {

        return $this->model->select ( 'reports.name' )
            ->where ( 'reports.available', "=", 1 )
            ->where ( 'reports.id', $id )
            // ->where ( 'reports.price', 0 )
            ->first ();
    }

    public function getEssentailReportId($id)
    {
        return $this->model->select ( 'reports.id' )
            ->where ( 'reports.id', $id )
            // ->where ( 'reports.price', 0 )
            ->first ();
    }

    public function getAvailableReports($request)
    {
        return $this->model->select ( 'reports.*' )
            ->where ( 'reports.available', "=", 1 )
            ->whereNotIn ( 'reports.woo_id', $request )
            ->groupBy ( 'reports.name' )->orderBy ( 'woo_id', 'DESC' )->get ();
    }

    public function getReportNameList($request)
    {
        $model = $this->model->select ( 'reports.id', 'reports.name' );
        \Log::info ( $request->interactive );
        if ( $request->interactive == 1 ) {
            $model = $model->join ( 'interactive_reports', 'reports.id', 'interactive_reports.report_id' );
        }
        $model->where ( 'reports.available', "=", 1 );
        return $model->groupBy ( 'reports.name' )->orderBy ( 'reports.woo_id', 'DESC' )->get ();
    }

    public function getReportsByCategory($search, $ids)
    {
        $model = $this->model->select ( 'reports.*' );
        $model = $model->whereNotIn ( 'reports.woo_id', $ids );
        if ( $search != null ) {
            $model = $model->where ( 'reports.category', '=', $search );
        }
        $model = $model->where ( 'reports.available', "=", 1 );
        return $model->groupBy ( 'reports.name' )->orderBy ( 'reports.woo_id', 'DESC' )->get ();
    }

    /**
     * Returns report info
     *
     * @return mixed
     */
    public function reportInfo($request)
    {
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->perPage = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '15';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'updated_at';
        $this->limit = (!empty( $request[ 'limit' ] )) ? ($request[ 'limit' ]) : 100;
        $columns = ['*'];
        $or = false;

        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );

        $model = $this->model
            ->select ( "id", "name" );

        foreach ( $request as $field => $value ) {
            if ( $value instanceof \Closure ) {
                $model = (!$or)
                    ? $model->where ( $value )
                    : $model->orWhere ( $value );
            } elseif ( is_array ( $value ) ) {
                if ( count ( $value ) === 3 ) {
                    list( $field, $operator, $search ) = $value;
                    $model = (!$or)
                        ? $model->where ( $field, $operator, $search )
                        : $model->orWhere ( $field, $operator, $search );
                } elseif ( count ( $value ) === 2 ) {
                    list( $field, $search ) = $value;
                    $model = (!$or)
                        ? $model->where ( $field, 'like', '%' . $value . '%' )
                        : $model->orWhere ( $field, '=', $search );
                }
            } else {
                $model = (!$or)
                    ? $model->where ( $field, 'like', '%' . $value . '%' )
                    : $model->orWhere ( $field, '=', $value );
            }
        }

        $result = $model
            ->where ( "name", "!=", "" )
            ->orderBy ( $this->sortColumn, $this->sort )
            ->get ();

        if ( count ( $result ) == 0 ) {
            return false;
        }

        return $result;
    }

    /**
     * Returns all report info
     *
     * @return mixed
     */
    public function reportAllInfo($request)
    {
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->perPage = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '15';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'reports.woo_id';
        $this->limit = (!empty( $request[ 'limit' ] )) ? ($request[ 'limit' ]) : 100;
        $columns = ['*'];
        $or = false;

        $request = Helper::arrayUnset ( $request, ['sortType', 'perPage', 'sort', 'page'] );

        $model = $this->model
            ->select ( "reports.id", 'interactive_reports.summary as type', "reports.woo_id", "reports.name", "reports.category", "reports.updated_at", "reports.created_at", "reports.segment", "reports.state",
                'reports.publish_at', "reports.exempt",

                \DB::raw ( "(CASE WHEN reports.available = 1 THEN 'Active' WHEN reports.available = 2 THEN 'Inactive'  WHEN reports.available = 0 THEN 'Deactivated'  ELSE '' END) AS available" )
            )->leftjoin ( 'interactive_reports', 'interactive_reports.report_id', 'reports.id' );

        foreach ( $request as $field => $value ) {
            if ( $value instanceof \Closure ) {
                $model = (!$or)
                    ? $model->where ( $value )
                    : $model->orWhere ( $value );
            } elseif ( is_array ( $value ) ) {
                if ( count ( $value ) === 3 ) {
                    list( $field, $operator, $search ) = $value;
                    $model = (!$or)
                        ? $model->where ( $field, $operator, $search )
                        : $model->orWhere ( $field, $operator, $search );
                } elseif ( count ( $value ) === 2 ) {
                    list( $field, $search ) = $value;
                    $model = (!$or)
                        ? $model->where ( $field, 'like', '%' . $value . '%' )
                        : $model->orWhere ( $field, '=', $search );
                }
            } else {
                $model = (!$or)
                    ? $model->where ( $field, 'like', '%' . $value . '%' )
                    : $model->orWhere ( $field, '=', $value );
            }
        }

        $result = $model
            ->where ( "name", "!=", "" )
            ->orderBy ( $this->sortColumn, $this->sort )
            ->paginate ( $this->perPage );

        return $result;
    }


    public function reportSelectInfo($id)
    {
        $results = $this->model->select ( 'reports.id', "reports.woo_id as digital_woo_id", "reports.woo_id as digital_woo_id_name",
            "report_Hard_copy_detail.woo_id as hardcopy_woo_id", "report_Hard_copy_detail.woo_id as hardcopy_woo_name",
            "reports.category","reports.exempt",
            "reports.segment as  segments", 'states.id  as state_id', 'reports.state',
            'reports.name as report_name', 'reports.price as digital_price', 'report_Hard_copy_detail.price as hard_copy_price',
            'reports.description as marketing_descrption', 'reports.link as purchase_url', 'reports.cover as cover_image',
            'reports.summary_pdf as simple_exec_summary_pdf', 'reports.full_pdf  as simple_full_pdf',
            'reports.enterprise_pdf as  simple_enterprise_pdf',
            'interactive_reports.summary as interactive_summary',
            'interactive_reports.key_findings_text as interactive_key_findings_text',
            'interactive_reports.key_findings_list as interactive_key_findings_list',
            'interactive_reports.cannaclip as interactive_canaclip_url', 'interactive_reports.cannaclip as interactive_canaclip_img',
            'interactive_reports.author_headshot as author_photo', 'reports.available as publish', 'interactive_reports.summary as type'

        )
            ->leftJoin ( "report_Hard_copy_detail", "report_Hard_copy_detail.report_id", "reports.id" )
            ->leftJoin ( "interactive_reports", "reports.id", "interactive_reports.report_id" )
            ->leftJoin ( "charts", "charts.report_id", "reports.id", 'states.id  as state_id' )
            ->leftJoin ( "states", "states.name", "reports.state" )
            ->where ( "reports.id", $id )
            ->first ();

        $author = $this->getCreditUserDetils ( $id, 1 );
        $research = $this->getCreditUserDetils ( $id, 2 );
        $editor = $this->getCreditUserDetils ( $id, 3 );
        $markeing = $this->getCreditUserDetils ( $id, 4 );

        $objReport = collect ( $results );
        $objReport->put ( 'autohor', $author );
        $objReport->put ( 'research', $research );
        $objReport->put ( 'editor', $editor );
        $objReport->put ( 'markeing', $markeing );

        return $objReport;
    }

    public function getCreditUserDetils($reportId, $type)
    {
        $author = DB::table ( "credit_user_detail" )->where ( 'credit_user_detail.report_id', '=', $reportId )
            ->where ( 'credit_user_detail.type', '=', $type )
            ->select (
                [
                    'credit_user_id'
                ] )
            ->get ();

        $objResult = [];
        foreach ( $author as $result ) {

            $objResult[] = $result->credit_user_id;
        }
        return $objResult;

    }

    public function deleteReportRefTbl($reportId)
    {
        if ( $this->model->where ( "id", "=", $reportId )->delete () == true ) {

            $objInteractive = InteractiveReport::where ( "report_id", $reportId )->delete ();

            $chartsId = Chart::where ( "report_id", $reportId )->select ( 'id' )->first ();

            $objCharts = Chart::where ( "report_id", $reportId )->delete ();
            if ( !empty( $chartsId ) ) {
                $objChartsKeyWord = ChartKeywords::where ( "charts_id", $chartsId->id )->delete ();
            }

        }
        return true;
    }

    public function deleteReportChartTbl($reportId)
    {
        $chartsId = Chart::where ( "report_id", $reportId )->select ( 'id' )->first ();
        $objCharts = Chart::where ( "report_id", $reportId )->delete ();
        if ( !empty( $chartsId ) ) {
            $objChartsKeyWord = ChartKeywords::where ( "charts_id", $chartsId->id )->delete ();
        }
        return true;
    }

    public function saveHardCopyReportDetail($reportId, $wooId = null, $price = null)
    {

        DB::table ( "report_Hard_copy_detail" )->where ( "report_id", $reportId )->delete ();
        DB::table ( "report_Hard_copy_detail" )->insert ( ['report_id' => $reportId, 'woo_id' => $wooId, 'price' => $price] );
    }

    public function saveReport($report_array)
    {
        $publish_at = '';
        $woo_id = !empty( $report_array[ 'simple_digital_woo_id' ] ) ? $report_array[ 'simple_digital_woo_id' ] : 0;
        $price = !empty( $report_array[ 'simple_digital_price' ] ) ? $report_array[ 'simple_digital_price' ] : 0;
        $name = !empty( $report_array[ 'simple_report_name' ] ) ? $report_array[ 'simple_report_name' ] : '';
        $category = !empty( $report_array[ 'simple_report_category' ] ) ? $report_array[ 'simple_report_category' ] : 0;
        $segment = !empty( $report_array[ 'simple_report_segment' ] ) ? $report_array[ 'simple_report_segment' ] : '';
        $state = !empty( $report_array[ 'simple_state_name' ] ) ? $report_array[ 'simple_state_name' ] : '';
        $state_id = !empty( $report_array[ 'simple_state_id' ] ) ? $report_array[ 'simple_state_id' ] : 0;
        $business = !empty( $report_array[ 'simple_business' ] ) ? $report_array[ 'simple_business' ] : '';
        $link = !empty( $report_array[ 'simple_purchase_url' ] ) ? $report_array[ 'simple_purchase_url' ] : '';
        $description = !empty( $report_array[ 'simple_marketing_description' ] ) ? $report_array[ 'simple_marketing_description' ] : '';
        $exempt = !empty( $report_array[ 'exempt' ] ) ? $report_array[ 'exempt' ] : 0;
        $available = '';

        if ( isset( $report_array[ 'simple_publish' ] ) ) {

            $available = $report_array[ 'simple_publish' ];
        }


        if ( $available == 1 ) {
            $publish_at = date ( 'Y-m-d H:i:s' );

        }

        $objReport = new Report();
        $objReport->woo_id = $woo_id;
        $objReport->price = $price;
        $objReport->name = $name;
        $objReport->category = $category;
        $objReport->segment = $segment;
        $objReport->state = $state;
        $objReport->state_id = $state_id;
        $objReport->business = $business;
        $objReport->link = $link;
        $objReport->description = $description;
        $objReport->available = $available;
        $objReport->cover = '';
        $objReport->publish_at = $publish_at;
        $objReport->summary_pdf = '';
        $objReport->full_pdf = '';
        $objReport->enterprise_pdf = '';
        $objReport->exempt =$exempt;
        $objReport->save ();

        if ( !empty( $objReport->id ) && !empty( $report_array[ 'simple_hard_copy_woo_id' ] ) && !empty( $report_array[ 'simple_hardcopy_price' ] ) ) {

            $this->saveHardCopyReportDetail ( $objReport->id, $report_array[ 'simple_hard_copy_woo_id' ], $report_array[ 'simple_hardcopy_price' ] );
        }

        $objUploadReport = $this->uploadReport ( $report_array, 'POST', $objReport->id );

        if ( $objUploadReport[ 'success' ] == true && !empty( $objReport->id ) ) {

            return ['id' => $objReport->id,
                'simple_chart_keyword' => $objUploadReport[ 'simple_chart_keyword' ],
                'simple_chart_image_file' => $objUploadReport[ 'simple_chart_image_file' ],
                'extract_path' => $objUploadReport[ 'extract_path' ]
            ];
        } else {
            return false;
        }

    }

    public function updateReportExempt($data)
    {
        $exempt = !empty( $data[ 'exempt' ] ) ? $data[ 'exempt' ] : 0;
        $id = !empty( $data[ 'id' ] ) ? $data[ 'id' ] : 0;

        return $this->model->where ( 'id', '=', $id )
            ->update ( ['exempt' => $exempt,

            ] );

    }

    public function updateReport($report_array)
    {

        $objReport = Report::where ( "id", !empty( $report_array[ 'id' ] ) ? $report_array[ 'id' ] : '' )->first ();
        $publish_at = '';
        $woo_id = !empty( $report_array[ 'simple_digital_woo_id' ] ) ? $report_array[ 'simple_digital_woo_id' ] : !empty( $objReport->woo_id ) ? $objReport->woo_id : 0;
        $price = !empty( $report_array[ 'simple_digital_price' ] ) ? $report_array[ 'simple_digital_price' ] : 0;
        $name = !empty( $report_array[ 'simple_report_name' ] ) ? $report_array[ 'simple_report_name' ] : '';
        $category = !empty( $report_array[ 'simple_report_category' ] ) ? $report_array[ 'simple_report_category' ] : 0;
        $segment = !empty( $report_array[ 'simple_report_segment' ] ) ? $report_array[ 'simple_report_segment' ] : '';
        $state = !empty( $report_array[ 'simple_state_name' ] ) ? $report_array[ 'simple_state_name' ] : '';
        $state_id = !empty( $report_array[ 'simple_state_id' ] ) ? $report_array[ 'simple_state_id' ] : 0;
        $business = !empty( $report_array[ 'simple_business' ] ) ? $report_array[ 'simple_business' ] : '';
        $link = !empty( $report_array[ 'simple_purchase_url' ] ) ? $report_array[ 'simple_purchase_url' ] : '';
        $description = !empty( $report_array[ 'simple_marketing_description' ] ) ? $report_array[ 'simple_marketing_description' ] : '';
        $exempt = !empty( $report_array[ 'exempt' ] ) ? $report_array[ 'exempt' ] : 0;
        $available = '';

        if ( isset( $report_array[ 'simple_publish' ] ) ) {
            $available = $report_array[ 'simple_publish' ];
        } else {
            $available = $objReport->available;
        }

        if ( $available == 1 ) {
            $publish_at = date ( 'Y-m-d H:i:s' );
        }

        $this->model->where ( 'id', '=', $report_array[ 'id' ] )
            ->update ( ['name' => $name, 'category' => $category,
                'segment' => $segment, 'state' => $state,
                'state_id' => $state_id, 'business' => $business,
                'price' => $price,
                'link' => $link, 'description' => $description,
                'available' => $available,
                'publish_at' => $publish_at,
                'exempt' => $exempt

            ] );

        if ( !empty( $report_array[ 'id' ] ) && !empty( $report_array[ 'simple_hard_copy_woo_id' ] ) && !empty( $report_array[ 'simple_hardcopy_price' ] ) ) {

            $this->saveHardCopyReportDetail ( $report_array[ 'id' ], $report_array[ 'simple_hard_copy_woo_id' ], $report_array[ 'simple_hardcopy_price' ] );
        }


        $objUploadReport = $this->uploadReport ( $report_array, 'POST', !empty( $report_array[ 'id' ] ) ? ($report_array[ 'id' ]) : 0 );


        if ( $objUploadReport[ 'success' ] == true && !empty( !empty( $report_array[ 'id' ] ) ? ($report_array[ 'id' ]) : 0 ) ) {

            return ['id' => !empty( $report_array[ 'id' ] ) ? ($report_array[ 'id' ]) : 0, 'simple_chart_keyword' => $objUploadReport[ 'simple_chart_keyword' ], 'simple_chart_image_file' => $objUploadReport[ 'simple_chart_image_file' ],
                'extract_path' => $objUploadReport[ 'extract_path' ]
            ];
        } else {
            return false;
        }

    }


    public function uploadReport($report_array, $request = 'POST', $id = null, $flag = false)
    {

        $objReport = Report::find ( isset( $id ) ? $id : null );
        $data = [];
        $data[ 'success' ] = true;
        $data[ 'simple_chart_keyword' ] = null;
        $data[ 'simple_chart_image_file' ] = null;
        $data[ 'extract_path' ] = null;
        $flag = true;


        if ( !empty( $report_array[ 'simple_cover_image' ] ) ) {

            if ( gettype ( $report_array[ 'simple_cover_image' ] ) == 'object' ) {

                $image = $report_array[ 'simple_cover_image' ];
                $fileName = $image->getClientOriginalName ();

                if ( $request == 'PUT' ) {

                    $existImgName = $objReport->cover;
                    $imageName = explode ( "/", $existImgName );

                    if ( !empty( $imageName ) ) {

                        \File::delete ( public_path ( ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) ) . Config::get ( 'custom_config.REPORTS_COVER' )
                            . $imageName[ (count ( $imageName ) - 1) ] );
                    }

                }
                $destinationPath = ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_COVER' );

                if ( !file_exists ( $destinationPath ) ) {
                    mkdir ( $destinationPath, 0777, true );
                }

                $report_array[ 'simple_cover_image' ]->move ( $destinationPath, $fileName );

                $objReport->cover = $fileName;

                if ( $objReport->save () ) {


                    $flag = true;
                }

            }

        }


        if ( !empty( $report_array[ 'simple_chart_keyword' ] ) ) {

            if ( gettype ( $report_array[ 'simple_chart_keyword' ] ) == 'object' ) {
                $objHelper = new Helper();
                $destinationPath = ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_CHART_KEYWORD' );

                $image = $report_array[ 'simple_chart_keyword' ];
                $fileName = $objReport->id . "_"  . rand() . 'chart_keyword.' . $image->getClientOriginalExtension ();

                if ( $request == 'PUT' ) {

                    $existImgName = $objReport->id . "_" . 'chart_keyword';

                    $imageName = $objHelper->getlocationFileName ( $destinationPath, $existImgName );

                    if ( !empty( $imageName ) ) {

                        \File::delete ( public_path ( ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) ) . Config::get ( 'custom_config.REPORTS_CHART_KEYWORD' )
                            . $imageName );
                    }

                }

                if ( !file_exists ( $destinationPath ) ) {
                    mkdir ( $destinationPath, 0777, true );
                }

                $report_array[ 'simple_chart_keyword' ]->move ( $destinationPath, $fileName );
                $data[ 'simple_chart_keyword' ] = $destinationPath . $fileName;
                $flag = true;
            }


        }

        if ( !empty( $report_array[ 'simple_chart_image_file' ] ) ) {

            if ( gettype ( $report_array[ 'simple_chart_image_file' ] ) == 'object' ) {

                $objHelper = new Helper();
                $destinationPath = ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" );
                $destinationExtractPath = ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_CHART_FILENAME' );

                $image = $report_array[ 'simple_chart_image_file' ];
                $fileName = $objReport->id .  rand(). 'chart_img.' . $image->getClientOriginalExtension ();

                if ( $request == 'PUT' ) {

                    $existImgName = $objReport->id . 'chart_img';
                    $imageName = $objHelper->getlocationFileName ( $destinationPath, $existImgName );

                    if ( !empty( $imageName ) ) {

                        \File::delete ( public_path ( ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) )
                            . $imageName );
                    }

                }

                if ( !file_exists ( $destinationPath ) ) {
                    mkdir ( $destinationPath, 0777, true );
                }

                $report_array[ 'simple_chart_image_file' ]->move ( $destinationPath, $fileName );
                $data[ 'simple_chart_image_file' ] = $destinationPath . $fileName;
                $data[ 'extract_path' ] = $destinationExtractPath;
                $flag = true;
            }

        }


        if ( !empty( $report_array[ 'simple_exec_summary_pdf' ] ) ) {

            if ( gettype ( $report_array[ 'simple_exec_summary_pdf' ] ) == 'object' ) {

                $image = $report_array[ 'simple_exec_summary_pdf' ];
                $fileName = $image->getClientOriginalName ();

                if ( $request == 'PUT' ) {

                    $existPdfName = $objReport->summary_pdf;
                    $pdfeName = explode ( "/", $existPdfName );

                    if ( !empty( $pdfeName ) ) {


                        \File::delete ( public_path ( ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) ) . Config::get ( 'custom_config.REPORTS_SUMMERY_PDF' )
                            . "/" . $pdfeName[ (count ( $pdfeName ) - 1) ] );
                    }

                }
                $destinationPath = ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_SUMMERY_PDF' );

                if ( !file_exists ( $destinationPath ) ) {
                    mkdir ( $destinationPath, 0777, true );
                }

                $report_array[ 'simple_exec_summary_pdf' ]->move ( $destinationPath, $fileName );

                $objReport->summary_pdf = $fileName;
                if ( $objReport->save () ) {
                    $flag = true;
                }

            }

        }


        if ( !empty( $report_array[ 'simple_full_pdf' ] ) ) {

            if ( gettype ( $report_array[ 'simple_exec_summary_pdf' ] ) == 'object' ) {
                $image = $report_array[ 'simple_full_pdf' ];
                $fileName = $image->getClientOriginalName ();

                if ( $request == 'PUT' ) {

                    $existPdfName = $objReport->full_pdf;
                    $pdfeName = explode ( "/", $existPdfName );

                    if ( !empty( $pdfeName ) ) {


                        \File::delete ( public_path ( ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) ) . Config::get ( 'custom_config.REPORTS_FULL_PDF' )
                            . "/" . $pdfeName[ (count ( $pdfeName ) - 1) ] );
                    }

                }
                $destinationPath = ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_FULL_PDF' );

                if ( !file_exists ( $destinationPath ) ) {
                    mkdir ( $destinationPath, 0777, true );
                }

                $report_array[ 'simple_full_pdf' ]->move ( $destinationPath, $fileName );

                $objReport->full_pdf = $fileName;
                if ( $objReport->save () ) {
                    $flag = true;
                }
            }

        }

        if ( !empty( $report_array[ 'simple_enterprise_pdf' ] ) ) {
            if ( gettype ( $report_array[ 'simple_enterprise_pdf' ] ) == 'object' ) {

                $image = $report_array[ 'simple_enterprise_pdf' ];
                $fileName = $image->getClientOriginalName ();

                if ( $request == 'PUT' ) {

                    $existPdfName = $objReport->enterprise_pdf;
                    $pdfeName = explode ( "/", $existPdfName );

                    if ( !empty( $pdfeName ) ) {

                        \File::delete ( public_path ( ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) ) . Config::get ( 'custom_config.REPORTS_ENTERPRISES_PDF' )
                            . "/" . $pdfeName[ (count ( $pdfeName ) - 1) ] );
                    }

                }
                $destinationPath = ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_ENTERPRISES_PDF' );

                if ( !file_exists ( $destinationPath ) ) {
                    mkdir ( $destinationPath, 0777, true );
                }

                $report_array[ 'simple_enterprise_pdf' ]->move ( $destinationPath, $fileName );

                $objReport->enterprise_pdf = $fileName;
                if ( $objReport->save () ) {
                    $flag = true;
                }

            }

        }

        return $data;
    }


    public function getAllZeroPriceReportIds()
    {
        $model = $this->model->select ( 'reports.woo_id' )
            ->where ( 'reports.price', '=', 0 );
        $model = $model->where ( 'reports.available', "=", 1 );
        $model = $model->pluck ( 'woo_id' );
        return $model;

    }

    public function getAllSegmentZeroPriceReportIds($segment)
    {
        $model = $this->model->select ( 'reports.woo_id' )
            ->where ( 'reports.price', '=', 0 );
        $model = $model->where ( 'reports.available', "=", 1 );
        $model = $model->where ( 'reports.segment', "=", $segment );
        $model = $model->pluck ( 'woo_id' );
        return $model;

    }

    public function getHardCopyDetails($id)
    {
        $hardCopyData = DB::table ( "report_Hard_copy_detail" )->where ( 'report_Hard_copy_detail.report_id', '=', $id )
            ->select (
                [
                    'report_Hard_copy_detail.*'
                ] )
            ->first ();
        return $hardCopyData;
    }

    public function getPurchasedReportData($idArray)
    {
        $result = $this->model->select ( 'reports.*' );
        $result = $result->whereIn ( 'reports.woo_id', $idArray )
            ->orderBy ( 'woo_id', 'DESC' )->get ();
        return $result;
    }

    public function searchPurchasedReportData($request, $purchasedOrder, $user, $purchasedOrder_prum_enterprise = null)
    {
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'desc';
        $this->perPage = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '12';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'reports.woo_id';
        $searchType = (!empty( $request[ 'searchType' ] )) ? ($request[ 'searchType' ]) : null;
        $searchData = (!empty( $request[ 'search' ] )) ? ($request[ 'search' ]) : null;
        $model = $this->model
            ->select ( "reports.*" );
        if ( $searchType != null && $searchData != null ) {
            if ( $searchType == 1 ) {
                $model = $model->where ( 'reports.id', '=', $searchData );
            } elseif ( $searchType == 2 ) {
                $model = $model->where ( 'reports.name', 'like', '%' . $searchData . '%' );
            }
        }

        if ( $purchasedOrder != null ) {
            $model = $model->whereIn ( "reports.woo_id", $purchasedOrder );
        } else {

            //Not essentail user showing only availble = 1 & excepmt= 1 records(premium&plus/enterprise)
            $model = $model->where ( "reports.available", 1 )
                          ->where ( "reports.exempt", 1 );
            if ( $purchasedOrder_prum_enterprise != null ) {

                $model = $model->orWhere ( function ($query) use ($purchasedOrder_prum_enterprise) {
                    $query->whereIn ( "reports.woo_id", $purchasedOrder_prum_enterprise );
                       // ->where ( "reports.available", '=', 1 );


                } );
            }

        }


        $result = $model
            ->groupBy ( 'reports.name' )
            ->orderBy ( $this->sortColumn, $this->sort )
            ->paginate ( $this->perPage );
        $result->map ( function ($report) use ($user) {
            $report[ 'cover' ] = $report->getCoverImageAttribute ( $report->cover );
            unset( $report[ 'type' ] );
            if ( $user->subscription_level == Config::get ( 'custom_config.PACKAGE_PREMIUMPLUS' ) || $user->subscription_level == Config::get ( 'custom_config.PACKAGE_ENTERPRISE' ) ) {
                $report[ 'pdf_type' ] = Config::get ( 'custom_config.REPORT_TYPE.REPORTS_ENTERPRISES_PDF' );
                $report[ 'file_title' ] = $report->enterprise_pdf;
            } elseif ( $user->subscription_level == Config::get ( 'custom_config.PACKAGE_PREMIUM' ) || $user->subscription_level == Config::get ( 'custom_config.PACKAGE_ESSENTIAL' ) ) {
                $report[ 'pdf_type' ] = Config::get ( 'custom_config.REPORT_TYPE.REPORTS_FULL_PDF' );
                $report[ 'file_title' ] = $report->full_pdf;
            }
            return $report;
        } );
        return $result;
    }


    public function searchPurchasedReportName($purchasedOrder, $purchasedOrder_prum_enterprise = null, $user_subs_level = null)
    {
        $model = $this->model->select ( 'reports.name', 'reports.id' );
        if ( $purchasedOrder != null ) {
            $model = $model->whereIn ( 'reports.woo_id', $purchasedOrder );
        }
        $user_subs_level_ = null;
        if ( $user_subs_level_ == Config::get ( 'custom_config.PACKAGE_PREMIUMPLUS' ) || $user_subs_level == Config::get ( 'custom_config.PACKAGE_ENTERPRISE' )
            || $user_subs_level == Config::get ( 'custom_config.PACKAGE_PREMIUM' )

        ) {

            $model = $model->where ( "reports.available", 1 )
                ->where ( "reports.exempt", 1 );

            if ( $purchasedOrder_prum_enterprise != null ) {

                $model = $model->orWhere ( function ($query) use ($purchasedOrder_prum_enterprise) {
                    $query->whereIn ( "reports.woo_id", $purchasedOrder_prum_enterprise );
                        //->where ( "reports.available", '=', 1 );


                } );


            }
        }


        $model = $model->groupBy ( 'reports.name' )
            ->orderBy ( 'woo_id', 'DESC' )->get ();

        return $model;
    }

    public function searchAvailableReportData($request, $purchasedOrder, $user)
    {
        $this->sort = (!empty( $request[ 'sort' ] )) ? ($request[ 'sort' ]) : 'DESC';
        $this->perPage = (!empty( $request[ 'perPage' ] )) ? ($request[ 'perPage' ]) : '12';
        $this->sortColumn = (!empty( $request[ 'sortType' ] )) ? ($request[ 'sortType' ]) : 'reports.woo_id';
        $searchType = (!empty( $request[ 'searchType' ] )) ? ($request[ 'searchType' ]) : null;
        $searchData = (!empty( $request[ 'search' ] )) ? ($request[ 'search' ]) : null;
        $searchByCategoryData = (!empty( $request[ 'searchByCategory' ] )) ? ($request[ 'searchByCategory' ]) : null;
        $model = $this->model
            ->select ( "reports.*", "report_Hard_copy_detail.price as hard_copy_price", "report_Hard_copy_detail.woo_id as hard_woo_id" )
            ->leftJoin ( "report_Hard_copy_detail", "report_Hard_copy_detail.report_id", "reports.id" );
        if ( $searchType != null && $searchData != null ) {
            if ( $searchType == 1 ) {
                $model = $model->where ( 'reports.id', '=', $searchData );
            } elseif ( $searchType == 2 ) {
                $model = $model->where ( 'reports.name', 'like', '%' . $searchData . '%' );
            }
        }
        if ( $purchasedOrder != null ) {
            $model = $model->whereNotIn ( "reports.woo_id", $purchasedOrder );
        }

        if ( $searchByCategoryData != null ) {
            $model = $model->where ( "reports.category", "=", $searchByCategoryData );
        }

        $result = $model
            ->where ( "reports.available", 1 )
            ->groupBy ( 'reports.name' )
            ->orderBy ( $this->sortColumn, $this->sort )
            ->paginate ( $this->perPage );
        $result->map ( function ($report) use ($user) {
            $report[ 'cover' ] = $report->getCoverImageAttribute ( $report->cover );
            $report[ 'summery_pdf' ] = $report->getCoverImageAttribute ( $report->summary_pdf );
            $report[ 'wooId' ] = $report->woo_id;
            $report[ 'file_title' ] = $report->summary_pdf;
            //Hard Copy Data
            if ( $report->woo_id != null ) {
                $report[ 'hardCopyPrice' ] = $report->hard_copy_price;
                $report[ 'hardCopyWooId' ] = $report->hard_woo_id;
            } else {
                $report[ 'hardCopyPrice' ] = null;
                $report[ 'hardCopyWooId' ] = "NA";
            }
            unset( $report[ 'type' ] );
            $report[ 'pdf_type' ] = Config::get ( 'custom_config.REPORT_TYPE.REPORTS_SUMMERY_PDF' );
            return $report;
        } );

        return $result;
    }


    public function searchAvailableReportName($purchasedOrder, $searchByCategoryData = null)
    {
        $model = $this->model->select ( 'reports.name', 'reports.id' );
        if ( $purchasedOrder != null ) {
            $model = $model->whereNotIn ( 'reports.woo_id', $purchasedOrder );
        }

        if ( $searchByCategoryData != null ) {
            $model = $model->where ( "reports.category", "=", $searchByCategoryData );
        }
        $model = $model->groupBy ( 'reports.name' )
            ->where ( "reports.available", 1 )
            ->orderBy ( 'woo_id', 'DESC' )->get ();

        return $model;
    }

    /**
     * get reports name by woo id
     * @param array $reportIds
     * @return array
     */
    public function getReportNames($reportIds = array()){

        $reportNames = array();

        $reportNames = $this->model
            ->select(DB::raw('CONCAT(woo_id, " - ",name) AS name'))
            ->whereIn('woo_id', $reportIds)
            ->get();

        return $reportNames->toArray();
    }

}
