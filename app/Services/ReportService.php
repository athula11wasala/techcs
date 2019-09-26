<?php

namespace App\Services;

use App\Repositories\ReportRepository;
use App\Repositories\Criteria\Users\OrderByCreated;
use App\Repositories\UserRepository;
use App\Repositories\ChartsRepository;
use App\Repositories\InteractiveReportRepository;
use App\Repositories\CreditUserRepository;
use App\Models\InteractiveReport;
use Join;

class ReportService
{

    private $reportRepository;
    private $chartRepository;
    private $interactiveReportRepository;
    private $creditUserReportRepository;

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(ReportRepository $reportRepository, ChartsRepository $chartRepository, InteractiveReportRepository $interactiveReportRepository, CreditUserRepository $creditUserReportRepository)
    {
        $this->reportRepository = $reportRepository;
        $this->chartRepository = $chartRepository;
        $this->interactiveReportRepository = $interactiveReportRepository;
        $this->creditUserReportRepository = $creditUserReportRepository;
    }

    public function createReport($reportArray)
    {

        set_time_limit(1000);
        ini_set('memory_limit', '-1');
        $reportObject['simple_chart_image_file'] = '';
        $reportObject['simple_chart_keyword'] = '';
        $reportObject['extract_path'] = '';
        $reportObject = $this->reportRepository->saveReport($reportArray);

        if (isset($reportObject['id'])) {

            if ($reportArray['report_type'] == 'interactive') {

                $interactvieReport = $this->interactiveReportRepository->saveReport($reportArray, $reportObject['id']);
            } else {
                $interactvieReport = $reportObject['id'];
            }


            if ($interactvieReport == null) {

                $this->reportRepository->deleteReportRefTbl($reportObject['id']);

                return false;
            }


            $ChartResponse = $this->chartRepository->saveChart($reportObject['id'], $reportObject['simple_chart_keyword'], $reportObject['simple_chart_image_file'], $reportObject['extract_path']);
            if (isset($ChartResponse['success']) && $ChartResponse['success'] == true) {

                //   print_r($objChartResponse['reportId']);
                return true;
            } else {

                $this->reportRepository->deleteReportRefTbl($reportObject['id']);
                if (isset($ChartResponse['message'])) {

                    return ['success' => 'fail', 'message' => $ChartResponse['message']];

                }

                return false;
            }

        } else {

            $this->reportRepository->deleteReportRefTbl($reportObject['id']);
            return false;
        }


        return false;
    }

    public function updateReportExempt($data){

        return   $this->reportRepository->updateReportExempt($data);
    }

    public function updateReport($reportArray)
    {

        set_time_limit(1000);
        ini_set('memory_limit', '-1');
        $reportObject['simple_chart_image_file'] = '';
        $reportObject['simple_chart_keyword'] = '';
        $reportObject['extract_path'] = '';
        $reportObject = $this->reportRepository->updateReport($reportArray);

        if (isset($reportObject['id'])) {

            if ($reportArray['report_type'] == 'interactive') {

                $objInteractive = InteractiveReport::where("report_id", $reportObject['id'])->first();

                if (!empty($objInteractive)) {

                    $interactvieReport = $this->interactiveReportRepository->UpdateReport($reportArray, $reportObject['id'], !empty($objInteractive->id) ? $objInteractive->id : null);
                } else {

                    $interactvieReport = $this->interactiveReportRepository->saveReport($reportArray, $reportObject['id']);
                }

                if ($interactvieReport == null) {

                    $this->reportRepository->deleteReportRefTbl($reportObject['id']);

                    return false;
                }
            }

            $excel =  !empty($reportArray[ 'simple_chart_keyword' ]) ?  $reportArray[ 'simple_chart_keyword' ]:'';

            if ( gettype ( $excel ) == 'object' ) {

                $ChartResponse = $this->chartRepository->saveChart($reportObject['id'], $reportObject['simple_chart_keyword'], $reportObject['simple_chart_image_file'], $reportObject['extract_path']);
            }
             else {
                 $ChartResponse = $this->chartRepository->saveChartZip($reportObject['id'], $reportObject['simple_chart_keyword'], $reportObject['simple_chart_image_file'], $reportObject['extract_path']);
             }

            if (isset($ChartResponse['success']) && $ChartResponse['success'] == true) {

                //   print_r($objChartResponse['reportId']);
                return true;
            } else {

                //$this->reportRepository->deleteReportChartTbl($reportObject['id']);

                if (isset($ChartResponse['message'])) {

                    return ['success' => 'fail', 'message' => $ChartResponse['message']];

                }

                return false;
            }

        } else {

            $this->reportRepository->deleteReportRefTbl($reportObject['id']);
            return false;
        }

        return false;
    }

    public function getReortDetail($request)
    {
        return $this->reportRepository->reportAllInfo($request->all());
    }

    public function getSelectReortDetail($id)
    {

        if (!empty($id)) {
            return $this->reportRepository->reportSelectInfo($id);
        }
        return false;

    }


    public function getCreditUserDetail()
    {
        return $this->creditUserReportRepository->allCreditUserInfo();
    }


    public function getHardCopyDetails($id)
    {
        return $this->reportRepository->getHardCopyDetails($id);
    }


    public function getPurchasedReportData($purchasedArray)
    {
        return $this->reportRepository->getPurchasedReportData($purchasedArray);
    }

    public function searchPurchasedReportData($request, $purchasedOrder, $user,$purchasedOrder_prum_enterprise = null)
    {

        return $this->reportRepository->searchPurchasedReportData($request, $purchasedOrder, $user,$purchasedOrder_prum_enterprise);
    }

    public function searchPurchasedReportName($purchasedOrder,$purchasedOrder_prum_enterprise = null,$user_subs_level =null)
    {
        return $this->reportRepository->searchPurchasedReportName($purchasedOrder,$purchasedOrder_prum_enterprise,$user_subs_level);
    }


    public function searchAvailableReportData($request, $purchasedOrder, $user)
    {
        return $this->reportRepository->searchAvailableReportData($request, $purchasedOrder, $user);
    }

    public function searchAvailableReportName($purchasedOrder,$searchAvailableReportName=null)
    {
        return $this->reportRepository->searchAvailableReportName($purchasedOrder,$searchAvailableReportName);
    }


}


