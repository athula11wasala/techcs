<?php
/**
 * Created by PhpStorm.
 * User: thilan
 * Date: 7/4/18
 * Time: 3:24 PM
 */

namespace App\Services;


use App\Models\Blog;
use App\Models\Chart;
use App\Models\InteractiveReport;
use App\Models\TopFive;
use App\Models\Webinar;
use App\Models\Cannaclips;
use App\Models\Profiles;
use App\Models\Report;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use App\Transformers\TopFiveTransformer;
use App\Repositories\TopFiveRepository;
use App\Equio\Helper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchService
{

    private $secret;
    private $url;
    private $tblName;
    private $id, $name, $thumbnail, $code, $pdfUrl, $description, $access, $postedBy, $readMore, $report_name, $videourl, $videotbl, $status;

    public function searchByScore($model, $search = null, $paginate, $status = false)
    {
        $this->status = $status;
        $columNameWithVal = $this->SearchModelArr ( $model, $search );
        //$currentDB = \DB::getDatabaseName ();
        if ( !empty( $columNameWithVal ) ) {


            $tblName = $model->getTable ();
            if ( $tblName == "reports" ) {
                $report = $model;
            }

            $model = $model;
            $model = $model->search ( $columNameWithVal )
                ->selectRaw ( "$this->id" )
                ->selectRaw ( " $this->name as name" )
                ->selectRaw ( "$this->code as code" )
                ->selectRaw ( "$this->thumbnail as thumbnail" )
                ->selectRaw ( "$this->pdfUrl as pdfurl" )
                ->selectRaw ( "$this->description as description" )
                ->selectRaw ( "$this->readMore as readmore" )
                ->selectRaw ( "(CASE WHEN $this->access = true THEN true ELSE false END) AS access" )
                ->selectRaw ( "$this->postedBy as postedby" );

            if ( !empty( $this->tblName == 'insight_daily_us' ) ) {
                $model = $model->selectRaw ( "date" )
                    ->selectRaw ( "topic" );
            }
            if ( !empty( $this->tblName == 'charts' ) ) {
                $model = $model->selectRaw ( "$this->report_name as report_name" )
                    ->leftjoin ( "reports", 'charts.report_id', 'reports.id' );
            }

            if ( $tblName == "reports" ) {
                if ( Helper::checkEssentailLogAccess () == false ) {
                    $model = $model->where ( "available", 1 );//->where ( "reports.exempt", 1 );
                }

            }

            $model = $model->Paginate ( $paginate );

            return $model;
        }
        return false;
    }

    public function addEssentailUserPurchseReport($model, $search = null, $paginate, $status = false)
    {
        $objHelper = new Helper();
        $purchserReport = $objHelper->getUserPurchaserdReport ();
        $retArrReportId = [];
        $this->status = $status;
        $columNameWithVal = $this->SearchModelArr ( $model, $search );

        if ( !empty( $columNameWithVal ) ) {

            $tblName = $model->getTable ();
            $model = $model;
            $model = $model->search ( $columNameWithVal )
                ->selectRaw ( "id" );
            $model = $model->orderby ( "id", "asc" )->get ();
            foreach ( $model as $rows ) {
                if ( in_array ( $rows->id, $purchserReport ) ) {
                    $retArrReportId[] = $rows->id;
                }
            }
            return $retArrReportId;

        }


    }


    public function checkReportAvailble($id = null)
    {
        $result = [];
        $resultObj = DB::table ( "reports" )->select ( 'reports.available' )
            // ->where('reports.available', "=", 1)
            ->where ( 'reports.id', $id )
            ->first ();

        if ( isset( $resultObj->available ) ) {
            return $resultObj->available;
        }
        return null;

    }

    public function checkReportExcempt($id = null)
    {
        $result = [];
        $resultObj = DB::table ( "reports" )->select ( 'reports.exempt' )
            ->where ( 'reports.id', $id )
            ->first ();

        return $resultObj->exempt;

    }

    public function searchVideoByScore($webinar = null, $cannaclips = null, $search = null, $paginate)
    {
        $columNameWithVal = [];
        if ( $webinar != null ) {

            $columNameWithVal = $this->SearchModelArr ( $webinar, $search );
        }
        if ( !empty( $columNameWithVal ) ) {

            $webinarObj = $webinar::search ( $columNameWithVal )
                ->selectRaw ( "id" )
                ->selectRaw ( " $this->name as name" )
                ->selectRaw ( "$this->code as code" )
                ->selectRaw ( "$this->thumbnail as thumbnail" )
                ->selectRaw ( "$this->videourl as video" )
                ->selectRaw ( "$this->pdfUrl as pdfurl" )
                ->selectRaw ( "$this->description as description" )
                ->selectRaw ( "$this->readMore as readmore" )
                ->selectRaw ( "(CASE WHEN $this->access = true THEN true ELSE false END) AS access" )
                ->selectRaw ( " 'webinars' AS videotbl" )
                ->selectRaw ( "$this->postedBy as postedby" )->get ();

            $columNameWithVal = [];;
            $columNameWithVal = $this->SearchModelArr ( $cannaclips, $search );
            if ( !empty( $columNameWithVal ) ) {
                $cannclipObj = $cannaclips::search ( $columNameWithVal )
                    ->selectRaw ( "id" )
                    ->selectRaw ( " $this->name as name" )
                    ->selectRaw ( "$this->code as code" )
                    ->selectRaw ( "$this->thumbnail as thumbnail" )
                    ->selectRaw ( "$this->videourl as video" )
                    ->selectRaw ( "$this->pdfUrl as pdfurl" )
                    ->selectRaw ( "$this->description as description" )
                    ->selectRaw ( "$this->readMore as readmore" )
                    ->selectRaw ( "(CASE WHEN $this->access = true THEN true ELSE false END) AS access" )
                    ->selectRaw ( " 'cannaclips' AS videotbl" )
                    ->selectRaw ( "$this->postedBy as postedby" )
                    ->get ();;
                $columNameWithVal = [];
            }


            $combineArr = array_merge ( $webinarObj->toArray (), $cannclipObj->toArray () );
            $responseData = ($this->paginate ( $combineArr, $paginate, 'videoResponseArr' ));
            return $responseData;

        }
        return false;
    }


    public function SearchModelArr($model, $parm = null)
    {
        $arr = [];
        if ( is_object ( $model ) ) {
            $tblName = $model->getTable ();

            if ( isset( $tblName ) ) {
                $configColumArr = [];
                switch ($tblName) {
                    case "insight_daily_us":
                        $configColumArr = Config::get ( 'searchscore.TOP5' );
                        $this->searchFieldMapper ( $tblName );
                        break;
                    case "blogs":
                        $configColumArr = Config::get ( 'searchscore.BLOG' );
                        $this->searchFieldMapper ( $tblName );
                        break;
                    case "charts":
                        $configColumArr = Config::get ( 'searchscore.CHART' );
                        $this->searchFieldMapper ( $tblName );
                        break;
                    case "profiles":
                        $configColumArr = Config::get ( 'searchscore.PROFILE' );
                        $this->searchFieldMapper ( $tblName );
                        break;
                    case "cannaclips":
                        $configColumArr = Config::get ( 'searchscore.CHANNACLIP' );
                        $this->searchFieldMapper ( $tblName );
                        break;
                    case "webinars":
                        $configColumArr = Config::get ( 'searchscore.WEBINARS' );
                        $this->searchFieldMapper ( $tblName );
                        break;
                    case "reports":
                        $configColumArr = Config::get ( 'searchscore.REPORT' );
                        $this->searchFieldMapper ( $tblName );
                        break;
                    default:
                        $configColumArr = '';
                }
                if ( is_array ( $configColumArr ) ) {
                    foreach ( $configColumArr[ 'columns' ] as $key => $rows ) {
                        $arr [ $key ] = '%' . $parm . '%';
                    }
                }

            }

        }
        return $arr;
    }

    private function searchFieldMapper($table)
    {
        switch ($table) {
            case "insight_daily_us":

                $this->tblName = $table;
                $this->id = $this->tblName . '.id';
                $this->name = $this->tblName . '.headline';
                $this->thumbnail = $this->tblName . '.image_url';
                $this->readMore = $this->tblName . '.source_url';
                $this->code = 'null';
                $this->pdfUrl = 'null';
                $this->date = $this->tblName . '.date';
                $this->description = $this->tblName . '.full_story';
                if ( $this->status == 1 ) {
                    $this->access = 'true';
                } else {
                    $this->access = 'false';
                }
                $this->postedBy = 'null';

                break;
            case "blogs":

                $this->tblName = $table;
                $this->id = $this->tblName . '.id';
                $this->name = $this->tblName . '.title';
                $this->thumbnail = $this->tblName . '.image_url';
                $this->readMore = $this->tblName . '.link';
                $this->code = 'null';
                $this->pdfUrl = 'null';
                $this->description = $this->tblName . '.description';
                if ( $this->status == 1 ) {
                    $this->access = 'true';
                } else {
                    $this->access = 'false';
                }

                $this->postedBy = 'null';

                break;
            case "charts":
                $this->tblName = $table;
                $this->id = $this->tblName . '.id';
                $this->name = $this->tblName . '.title';
                $this->thumbnail = $this->tblName . '.chartfilename';
                $this->readMore = 'null';
                $this->code = 'null';
                $this->report_name = 'reports.name';
                $this->pdfUrl = $this->tblName . '.reportfilename';
                $this->description = 'null';
                if ( Helper::checkChartSearchAccess () == true ) {
                    $this->access = 'true';
                } else {
                    $this->access = 'false';
                }
                $this->postedBy = 'null';

                break;
            case "profiles":

                $this->tblName = $table;
                $this->id = $this->tblName . '.id';
                $this->name = $this->tblName . '.name';
                $this->thumbnail = $this->tblName . '.cover';
                $this->readMore = 'null';
                $this->code = 'null';
                $this->pdfUrl = $this->tblName . '.full_pdf';
                $this->description = $this->tblName . '.description';
                if ( $this->status == 1 ) {
                    $this->access = 'true';
                } else {
                    $this->access = 'false';
                }
                $this->postedBy = 'null';

                break;
            case "reports":

                $this->tblName = $table;
                $this->id = $this->tblName . '.id';
                $this->name = $this->tblName . '.name';
                $this->thumbnail = $this->tblName . '.cover';
                $this->readMore = $this->tblName . '.link';
                $this->code = 'null';
                if ( Helper::checkEnterprisePdfAccess () == true ) {
                    $this->pdfUrl = $this->tblName . '.enterprise_pdf';
                } else {

                    $this->pdfUrl = $this->tblName . '.full_pdf';
                }
                $this->description = $this->tblName . '.description';
                $this->access = 'true';
                $this->postedBy = 'null';

                break;
            case "cannaclips":
                $this->tblName = $table;
                $this->id = $this->tblName . '.id';
                $this->name = $this->tblName . '.name';
                $this->thumbnail = $this->tblName . '.link';
                $this->videourl = $this->tblName . '.link';
                $this->readMore = 'null';
                $this->code = 'null';
                $this->pdfUrl = 'null';
                $this->description = $this->tblName . '.description';
                $this->access = 'true';
                $this->postedBy = 'null';
                $this->videotbl = 'cannaclips';

                break;
            case "webinars":
                $this->tblName = $table;
                $this->id = $this->tblName . '.id';
                $this->name = $this->tblName . '.title';
                $this->thumbnail = $this->tblName . '.link';
                $this->videourl = $this->tblName . '.link';
                $this->readMore = 'null';
                $this->code = 'null';
                $this->pdfUrl = 'null';
                $this->description = $this->tblName . '.description_short';
                if ( Helper::checkWebniarSearchAccess () == true ) {
                    $this->access = 'true';
                } else {
                    $this->access = 'false';
                }
                $this->postedBy = 'null';
                $this->videotbl = 'webinars';

                break;
            default:

        }
        return [];

    }

    public function weedGuideSearch()
    {
        return $this->weedGuildApiCall ();
    }

    private function weedGuildApiCall()
    {
        $client = new Client( ['base_uri' => Config::get ( 'custom_config.WEEDGUIDE_BASE_URI' )] );
        $res = $client->request ( Config::get ( 'custom_config.WEEDGUIDE_METHOD' ), Config::get ( 'custom_config.WEEDGUIDE_URI' ), [
            'headers' => [
                'x-api-key' => Config::get ( 'custom_config.WEEDGUIDE_X-API-KEY' )
            ]
        ], ['query' => ['start' => '0',
            'rows' => '10',
            'q' => '']] );

    }


    public function videoResponseArr($rows)
    {
        return [
            'id' => !empty( $rows[ 'id' ] ) ? $rows[ 'id' ] : '',
            'name' => !empty( $rows[ 'name' ] ) ? $rows[ 'name' ] : '',
            'code' => !empty( $rows[ 'code' ] ) ? $rows[ 'code' ] : '',
            'thumbnail' => !empty( $rows[ 'thumbnail' ] ) ? $rows[ 'thumbnail' ] : '',
            'video' => !empty( $rows[ 'video' ] ) ? $rows[ 'video' ] : '',
            'pdfurl' => !empty( $rows[ 'pdfurl' ] ) ? $rows[ 'pdfurl' ] : '',
            'description' => !empty( $rows[ 'description' ] ) ? $rows[ 'description' ] : '',
            'readmore' => !empty( $rows[ 'readmore' ] ) ? $rows[ 'readmore' ] : '',
            'access' => !empty( $rows[ 'access' ] ) ? $rows[ 'access' ] : 0,
            'postedby' => !empty( $rows[ 'postedby' ] ) ? $rows[ 'postedby' ] : '',
            'videotbl' => !empty( $rows[ 'videotbl' ] ) ? $rows[ 'videotbl' ] : ''
        ];

    }

    public function paginate($resultData, $perPage = 15, $callMethod = null, $sortColumn = 'id', $sortType = '')
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage ();
        $temp_collection = new Collection( $resultData );
        if ( empty( $sortType ) ) {

            $sorted = $temp_collection->sortBy ( $sortColumn, true );
        } else {
            $sorted = $temp_collection->sortDesc ( $sortColumn, true );
        }
        $resultData = $sorted->values ()->all ();
        $collection = new Collection( $resultData );

        $currentPageSearchResults = $collection->slice ( ($currentPage - 1) * $perPage, $perPage )->all ();
        if ( $currentPage != 1 ) {
            if ( !empty( $callMethod ) ) {
                $customArr = [];
                foreach ( $currentPageSearchResults as $rows ) {
                    $customArr[] = $this->$callMethod ( $rows );
                }
                $currentPageSearchResults = $customArr;
            }

        }
        $page = $currentPage;
        return new LengthAwarePaginator(
            $currentPageSearchResults,
            count ( $collection ),//total count
            $perPage,//items per page
            $page,//current page
            ['path' => Paginator::resolveCurrentPath ()]
        );
    }

}