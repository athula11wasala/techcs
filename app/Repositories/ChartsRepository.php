<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\Chart;
use App\Models\Keyword;
use App\Models\ChartKeywords;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;
use Excel;
use App\DasetFactory\DatasetFactoryMethod;
use Chumper\Zipper;
use ZipArchive;


class ChartsRepository extends Repository
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
        return 'App\Models\Chart';
    }


    public function allChartInfo($request)
    {

        $this->perPage = Config::get('custom_config.CHART_PAGINATE_PER_PAGE');
        $this->sort = (!empty($request['sort'])) ? ($request['sort']) : 'desc';
        $this->sortColumn = (!empty($request['sortType'])) ? ($request['sortType']) : 'charts.title';

        if (!empty($request['perPage']) && !empty($request['perPage'] = 10)) {
            $this->perPage = $request['perPage'];
        }
        $request = Helper::arrayUnset($request, ['sortType', 'perPage', 'sort', 'page']);

        return $this->findWhere($request);
    }


    public function findWhere($where, $columns = ['*'], $or = false, $elect = false)
    {
        $this->applyCriteria();
        $model = $this->model;

        $model = $model->select('charts.id', 'charts.report_id', 'charts.title', 'charts.chartfilename',
          'charts.reportfilename', 'charts.available', 'charts.created_at', 'charts.updated_at', 'reports.name as reportname');
        $model = $model->join("reports", "charts.report_id", "reports.id");

        foreach ($where as $field => $value) {
            if ($value instanceof \Closure) {
                $model = (!$or)
                    ? $model->where($value)
                    : $model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);
                } elseif (count($value) === 2) {
                    list($field, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, 'like', '%' . $value . '%')
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (!$or)
                    ? $model->where($field, 'like', '%' . $value . '%')
                    : $model->orWhere($field, '=', $value);
            }
        }

        return $model->orderBy($this->sortColumn, $this->sort)
            ->paginate($this->perPage);
    }

    public function allSearchedCharts($request)
    {
        if (!empty($request['ids'])) {
            $type = $request['type'];
            if ($type == Config::get('custom_config.CHART_SEARCH_BY_KEYWORD')) {
                $allChartsKeywords = (array)$this->getChartKeywords($request)->toArray();
                return $this->model->whereIn("id", $allChartsKeywords)->paginate($this->perPage);
            } elseif ($type == Config::get('custom_config.CHART_SEARCH_BY_NAME')) {
                $ids = !empty($request['ids']) ? (array )$request['ids'] : [];
                return $this->model->select("charts.*","reports.*","charts.id as id","reports.name  as reportname")
                    ->join("reports", "charts.report_id", "reports.id")
                    ->whereIn("charts.report_id", $ids)->paginate($this->perPage);
            }
        } else {

            return $this->model->select("charts.*","reports.name  as reportname")
                ->leftjoin("reports", "charts.report_id", "reports.id")
                ->orderBy('charts.id')
                ->paginate($this->perPage);

        }

    }

    public function getChartKeywords($request)
    {
        $ids = !empty($request['ids']) ? (array )$request['ids'] : [];
        return Keyword::select(DB::raw('distinct charts_keywords.charts_id as id'))
            ->leftjoin("charts_keywords", "charts_keywords.keywords_id", "keywords.id")
            ->WhereIn("id", $ids)->get();

    }


    public function getChartNames()
    {
        return Chart::select('charts.id', 'charts.title')->get();
    }

    public function getChartListFromReportId($id)
    {

        return Chart::select('charts.*','reports.name as reportname')
            ->leftjoin("reports", "reports.id", "charts.report_id")
            ->where('charts.report_id', '=', $id)
            ->get();
    }


    public function deleteChartWithChartKeyWord($reportId)
    {


        $existChart = DB::table("charts")->select("id", "chartfilename")->where("report_id", $reportId)->get();

        DB::table("charts")->where("report_id", $reportId)->delete();
        foreach ($existChart as $rows) {

            DB::table("charts_keywords")->where("charts_id", $rows->id)->delete();


            if (!empty($rows->chartfilename)) {

                \File::delete(public_path(ltrim(Config::get('custom_config.REPORTS_STORAGE'), "/")) . Config::get('custom_config.REPORTS_CHART_FILENAME')
                    . $rows->chartfilename);
            }


        }


    }


    public function saveChart($reportId, $chartExcel = null, $chartImage = null, $extract_path)
    {

        if ($chartExcel == null || $chartImage == null) {
            return ['success' => true, "reportId" => $reportId];

        }

        $this->deleteChartWithChartKeyWord($reportId);

        $zipPath = base_path("public/" . $chartImage);
        \Zipper::make($zipPath)->extractTo($extract_path);

        $zipIMgName = [];
        $zip = new ZipArchive();
        $zip->open(base_path("public/" . $chartImage));

        for ($num = 0; $num < $zip->numFiles; $num++) {
            $fileInfo = $zip->statIndex($num);
            $zipIMgName [] = ($fileInfo['name']);

        }

        $objDatasetFac = new  DatasetFactoryMethod();
        $path = base_path("public/" . $chartExcel);

        $data = Excel::load($path, function ($reader) {
        })->get();


        $HeaderColumn = $data->first()->keys()->toArray();

        if (isset($HeaderColumn[0]) && isset($HeaderColumn[1]) && isset($HeaderColumn[2]) &&
            $HeaderColumn[0] == "chart_name" && $HeaderColumn[1] == "chart_file_name" && $HeaderColumn[2] == "keywords") {

            $insert = [];

            if (!empty($data) && $data->count()) {


                foreach ($data as $rows) {


                    if (!empty($rows['chart_name'])) {
                        $tags = array_map(function ($rows) use ($objDatasetFac, $reportId) {
                            return $objDatasetFac->makeDatasetRead($rows, ['id' => 1, 'reportId' => $reportId]);
                        }, (array)$rows);


                        $key = array_keys($tags);
                        $insert[] = $tags[$key[1]];

                    }

                }

                if (!empty($insert)) {

                    foreach ($insert as $rows) {

                        if (!in_array($rows['chartfilename'], $zipIMgName)) {
                            $this->deleteImg(!empty($zipIMgName) ? $zipIMgName : null, !empty($extract_path) ? $extract_path : null);
                            $this->deleteUploadFile(!empty($chartExcel) ? $chartExcel : null, !empty($chartImage) ? $chartImage : null);

                            return ['error' => true, "reportId" => $reportId, 'message' => 'There were uploded wrong ChartKeyWord File or ChartImg File'];

                        }

                        if (empty($rows['title'])) {

                            return ['error' => true, "reportId" => $reportId, 'message' => 'There were uploded wrong ChartKeyWord File or ChartImg File'];

                        }

                        $objCharts = new Chart();
                        $objCharts->report_id = $rows['report_id'];
                        $objCharts->title = $rows['title'];
                        $objCharts->chartfilename = $rows['chartfilename'];
                        $objCharts->reportname = $rows['reportname'];
                        $objCharts->reportfilename = $rows['reportfilename'];
                        $objCharts->keywords = $rows['keywords'];
                        $objCharts->available = $rows['available'];
                        $objCharts->save();

                        $chartLastInsertId = !empty($objCharts->id) ? $objCharts->id : null;
                        if (!empty($chartLastInsertId)) {

                            $this->addKeyWord($chartLastInsertId, !empty($rows['keywords']) ? $rows['keywords'] : []);
                        }


                    }

                    $this->deleteUploadFile(!empty($chartExcel) ? $chartExcel : null, !empty($chartImage) ? $chartImage : null);


                    return ['success' => true, "reportId" => $reportId];
                }

            }

        } else {


            $this->deleteUploadFile(!empty($chartExcel) ? $chartExcel : null, !empty($chartImage) ? $chartImage : null);

            return ['error' => true, "reportId" => $reportId, 'message' => 'There were uploded wrong ChartKeyWord'];

        }


    }


    public function saveChartZip($reportId, $chartExcel = null, $chartImage = null, $extract_path)
    {
        if ( $chartImage == null) {
            return ['success' => true, "reportId" => $reportId];

        }

        $zipPath = base_path("public/" . $chartImage);
        //\Zipper::make($zipPath)->extractTo($extract_path);

        $zipIMgName = [];
        $zip = new ZipArchive();
        $zip->open(base_path("public/" . $chartImage));

        for ($num = 0; $num < $zip->numFiles; $num++) {
            $fileInfo = $zip->statIndex($num);
            $zipIMgName [] = ($fileInfo['name']);

        }

        $objCharts =  Chart::where("report_id",$reportId)->select("chartfilename as img")->get();

        if(count($objCharts) == 0){

            return ['error' => true, "reportId" => $reportId, 'message' => '"The chart title does not match the title for this image in the keywords file. Please upload a revised keyword file first, then upload the new chart.'];

        }
        $dbChartImg = [];
        foreach ($objCharts as $rows)
        {
            $dbChartImg[$rows->img] = $rows->img;
        }



        /*check database chart images and zip*/

        foreach ($zipIMgName as $rows){

            if(!isset($dbChartImg[$rows]))
            {

                return ['error' => true, "reportId" => $reportId, 'message' => 'There were uploded wrong ChartImg File'];
            }
        }

        \Zipper::make($zipPath)->extractTo($extract_path);
        $this->deleteUploadFile(!empty($chartExcel) ? $chartExcel : null, !empty($chartImage) ? $chartImage : null);
        return ['success' => true, "reportId" => $reportId];

    }


    public function deleteImg($allUploadImg = null, $extract_path = null)
    {

        if (!empty($allUploadImg) && !empty($extract_path)) {

            foreach ($allUploadImg as $rows) {

                \File::delete(public_path("/$extract_path" . $rows)
                );

            }


        }

    }


    public function deleteUploadFile($chartExcel = null, $chartImage = null)
    {

        if (!empty($chartExcel)) {

            \File::delete(public_path("/" . $chartExcel)
            );
        }
        if (!empty($chartImage)) {

            \File::delete(public_path("/" . $chartImage)
            );
        }


    }


    public function addKeyWord($chartLastInsertId, $keyWords)
    {

        if (!empty($keyWords)) {

            $keyWordArr = explode(',', $keyWords);

            foreach ($keyWordArr as $rows) {

                $objKeyWord = DB::table("keywords")->select("id")->where("name", $rows)->first();

                if (empty($objKeyWord)) {
                    $objKeyWord = new Keyword();
                    $objKeyWord->name = $rows;
                    $objKeyWord->save();

                    $this->ChartKeyWord($chartLastInsertId, $objKeyWord->id);

                } else {
                    ;
                    $this->ChartKeyWord($chartLastInsertId, $objKeyWord->id);
                }


            }
            return true;

        } else {
            return false;
        }


    }

    public function ChartKeyWord($chartId, $keyWordId)
    {

        $objChartKeyWord = new ChartKeywords();
        $objChartKeyWord->charts_id = $chartId;
        $objChartKeyWord->keywords_id = $keyWordId;
        $objChartKeyWord->save();

    }


    public function getChartById($id)
    {
        return Chart::select(['*', 'chartfilename as cover'])->find($id);
    }

}
