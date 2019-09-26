<?php

namespace App\Repositories;

use App\Models\InteractiveReport;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Container\Container as App;
use Illuminate\Support\Facades\Config;

class InteractiveReportRepository extends Repository
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
        return 'App\Models\InteractiveReport';
    }

    public function getAllReports($searchType, $search, $segment, $ids)
    {
        $query = $this->model;
        $query = $query->select(
            'reports.name as report_name',
            'reports.id as report_id',
            'reports.summary_pdf',
            'reports.cover',
            'interactive_reports.*'
        );
        $query = $query->join('reports', 'reports.id', 'interactive_reports.report_id');
        $query = $query->where('reports.available', "=", 1);
        $query = $query->orderBy('reports.woo_id', 'DESC');


        if ($ids != null) {
            $query = $query->whereIn('reports.woo_id', $ids);
        }
        if ($segment != 0) {
            $query = $query->where('reports.segment', '=', $segment);
        }
        if ($searchType == 1 && $search != 0) {
            $query = $query->where('interactive_reports.report_id', '=', $search);
        }
        if ($searchType == 2) {
            $query = $query->orWhere('reports.name', 'like', '%' . $search . '%');
        }
        if ($searchType == 3) {
            $result = $query->paginate(1);
        } else {
            $result = $query->get();
        }
        return $result;
    }

    public function getInteractiveReportSearch($search, $type)
    {
        $query = $this->model;
        $result = null;
        $query = $query->select(
            'reports.name as report_name',
            'reports.id as report_id',
            'reports.summary_pdf',
            'reports.cover',
            'interactive_reports.*');
        $query = $query->join('reports', 'reports.id', 'interactive_reports.report_id');
        $query = $query->where('reports.available', "=", 1);

        if ($type == 1) {
            $query = $query->where('interactive_reports.report_id', '=', $search);
        }
        if ($type == 2) {
            $query = $query->orWhere('reports.name', 'like', '%' . $search . '%');
        }
        if ($type == 3) {
            $result = $query->paginate(1);
        } else {
            $result = $query->get();
        }
        return $result;
    }

    public function getAllReportsName($segment)
    {
        $query = $this->model;
        $query = $query->select('reports.name', 'reports.id');
        $query = $query->join('reports', 'reports.id', 'interactive_reports.report_id');
        $query = $query->where('reports.available', "=", 1);
        $query = $query->orderBy('reports.woo_id', 'DESC');

        if ($segment != 0) {
            $query = $query->where('reports.segment', '=', $segment);
        }
        $result = $query->get();
        return $result;
    }

    public function getCreditUserDescription($id, $reportId = null, $type = null)
    {

        $id = explode(",", $id);
        $desc = '';
        $result = DB::table("credit__user")->select('description', 'id', 'type')->whereIn('id', $id)->orderBy('id', 'desc')->get();

        DB::table("credit_user_detail")->where(['report_id' => $reportId, 'type' => $type])->delete();
        foreach ($result as $rows) {

            $this->addCreditUserDetil($rows->id, $rows->type, $reportId);

            $desc .= $rows->description;
            $desc .= ', ';
        }


        return $desc;

    }


    public function addCreditUserDetil($id, $type, $reprotId)
    {


        DB::table("credit_user_detail")->insert(['report_id' => $reprotId, 'credit_user_id' => $id, 'type' => $type,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);
    }


    public function deleteExitRecordsByReportId($reportId)
    {

        if (!empty($reportId)) {

            $objReport = DB::table("interactive_reports")->where("report_id", $reportId)->first();
            DB::table("credit_user_detail")->where("report_id", $reportId)->delete();
            DB::table("interactive_reports")->where("report_id", $reportId)->delete();

            if (!empty($objReport)) {

                $existImgName = $objReport->author_headshot;
                $imageName = explode("/", $existImgName);

                if (!empty($imageName)) {

                    \File::delete(public_path(ltrim(Config::get('custom_config.REPORTS_STORAGE'), "/")) . Config::get('custom_config.INTERACTIVE_AUTHOR')
                        . $imageName[(count($imageName) - 1)]);
                }

            }


        }

    }


    public function UpdateReport($report_array, $reportId, $interactveId = null)
    {


        $report_id = !empty($reportId) ? $reportId : 0;
        $reportObj = $this->model->where('report_id', '=', $report_id)->first();
        $cannaclip = !empty($report_array['interactive_canaclip_url']) ? $report_array['interactive_canaclip_url'] : '';
        //  $author_headshot = !empty( $report_array[ 'simple_report_name' ] ) ? $report_array[ 'simple_report_name' ] : '';
        $summary = !empty($report_array['interactive_summary']) ? $report_array['interactive_summary'] : '';

        $author = !empty($report_array['credit_author']) ? str_replace(['[', ']'], '', $report_array['credit_author']) : ''; ///arr
        $analysts = !empty($report_array['credit_author_research_analyst']) ? str_replace(['[', ']'], '', $report_array['credit_author_research_analyst']) : '';  ///arr
        $leader = !empty($report_array['credit_author_leader']) ? $report_array['credit_author_leader'] : '';   /////not pass
        $editor = !empty($report_array['credit_author_editor']) ? str_replace(['[', ']'], '', $report_array['credit_author_editor']) : '';  ///arr

        $marketing = !empty($report_array['credit_author_marketing_pr']) ? str_replace(['[', ']'], '', $report_array['credit_author_marketing_pr']) : '';  // arr
        $key_findings_text = !empty($report_array['interactive_key_findings_text']) ? $report_array['interactive_key_findings_text'] : '';
        $key_findings_list = !empty($report_array['interactive_key_findings_list']) ? $report_array['interactive_key_findings_list'] : '';

        if (!empty($report_id)) {

            DB::table("credit_user_detail")->where("report_id", $report_id)->delete();

        }

        if (!empty($author)) {

            $author = $this->getCreditUserDescription($author, $reportId, 1);
        }
        if (!empty($analysts)) {

            $analysts = $this->getCreditUserDescription($analysts, $reportId, 2);
        }
        if (!empty($editor)) {

            $editor = $this->getCreditUserDescription($editor, $reportId, 3);
        }
        if (!empty($marketing)) {

            $marketing = $this->getCreditUserDescription($marketing, $reportId, 4);
        }


        $objReport = $this->model->where('report_id', '=', $report_id)
            ->update(['cannaclip' => $cannaclip, 'summary' => $summary, 'author' => $author,
                'analysts' => $analysts, 'leader' => $leader, 'editor' => $editor, 'marketing' => $marketing,
                'key_findings_text' => $key_findings_text, 'key_findings_list' => $key_findings_list

            ]);


        if (!empty($report_array['credit_author_photo'])) {

            if (gettype($report_array['credit_author_photo']) == 'object') {


                if (!empty($reportObj)) {

                    $this->uploadReport($report_array, 'POST', !empty($reportObj->id) ? $reportObj->id : 0);
                }


            }

        }


        return !empty ($reportObj->report_id) ? $reportObj->report_id : null;

    }


    public function saveReport($report_array, $reportId)
    {

        $report_id = !empty($reportId) ? $reportId : 0;
        $cannaclip = !empty($report_array['interactive_canaclip_url']) ? $report_array['interactive_canaclip_url'] : '';
        //  $author_headshot = !empty( $report_array[ 'simple_report_name' ] ) ? $report_array[ 'simple_report_name' ] : '';
        $summary = !empty($report_array['interactive_summary']) ? $report_array['interactive_summary'] : '';

        $author = !empty($report_array['credit_author']) ? str_replace(['[', ']'], '', $report_array['credit_author']) : ''; ///arr
        $analysts = !empty($report_array['credit_author_research_analyst']) ? str_replace(['[', ']'], '', $report_array['credit_author_research_analyst']) : '';  ///arr
        $leader = !empty($report_array['credit_author_leader']) ? $report_array['credit_author_leader'] : '';   /////not pass
        $editor = !empty($report_array['credit_author_editor']) ? str_replace(['[', ']'], '', $report_array['credit_author_editor']) : '';  ///arr

        $marketing = !empty($report_array['credit_author_marketing_pr']) ? str_replace(['[', ']'], '', $report_array['credit_author_marketing_pr']) : '';  // arr
        $key_findings_text = !empty($report_array['interactive_key_findings_text']) ? $report_array['interactive_key_findings_text'] : '';
        $key_findings_list = !empty($report_array['interactive_key_findings_list']) ? $report_array['interactive_key_findings_list'] : '';


        $this->deleteExitRecordsByReportId(!empty($reportId) ? $reportId : 0);

        if (!empty($author)) {

            $author = $this->getCreditUserDescription($author, $reportId, 1);
        }
        if (!empty($analysts)) {

            $analysts = $this->getCreditUserDescription($analysts, $reportId, 2);
        }
        if (!empty($editor)) {

            $editor = $this->getCreditUserDescription($editor, $reportId, 3);
        }
        if (!empty($marketing)) {

            $marketing = $this->getCreditUserDescription($marketing, $reportId, 4);
        }


        $objReport = $this->model->create(['report_id' => $report_id, 'cannaclip' => $cannaclip, 'summary' => $summary, 'author' => $author,
            'analysts' => $analysts, 'leader' => $leader, 'editor' => $editor, 'marketing' => $marketing,
            'key_findings_text' => $key_findings_text, 'key_findings_list' => $key_findings_list
        ]);

        $this->uploadReport($report_array, 'POST', $objReport->id);

        return !empty ($objReport->report_id) ? $objReport->report_id : null;

    }

    public function uploadReport($report_array, $request = 'POST', $id = null, $flag = false)
    {

        $objReport = InteractiveReport::find(isset($id) ? $id : null);


        if (!empty($report_array['credit_author_photo'])) {

            if (gettype($report_array['credit_author_photo']) == 'object') {

                $image = $report_array['credit_author_photo'];
                $fileName = $objReport->id . "_" . (string)$image->getClientOriginalName();

                if ($request == 'PUT') {

                    $existImgName = $objReport->author_headshot;
                    $imageName = explode("/", $existImgName);

                    if (!empty($imageName)) {

                        \File::delete(public_path(ltrim(Config::get('custom_config.REPORTS_STORAGE'), "/")) . Config::get('custom_config.INTERACTIVE_AUTHOR')
                            . $imageName[(count($imageName) - 1)]);
                    }

                }
                $destinationPath = ltrim(Config::get('custom_config.REPORTS_STORAGE'), "/") . Config::get('custom_config.INTERACTIVE_AUTHOR');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $report_array['credit_author_photo']->move($destinationPath, $fileName);
                $objReport->author_headshot = $fileName;

                if ($objReport->save()) {
                    $flag = true;
                }
            }
            $flag = true;

        }

        return $flag;
    }


}
