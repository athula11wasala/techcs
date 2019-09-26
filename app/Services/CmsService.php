<?php

namespace App\Services;

use App\DasetFactory\DatasetFactoryMethod;
use App\Equio\Exceptions\EquioException;
use App\Repositories\BundleReoportRepository;
use App\Repositories\CannabisBenchmarksUsRepository;
use App\Repositories\CannibalizationRepository;
use App\Repositories\CompanyNewsRepository;
use App\Repositories\CountryRepository;
use App\Repositories\Criteria\Users\OrderByCreated;
use App\Repositories\DataSetRepository;
use App\Repositories\InteractiveReportRepository;
use App\Repositories\ReportRepository;
use App\Repositories\SaleProjectionsRepository;
use App\Repositories\StateLegalizedRepository;
use App\Repositories\TaxAlertRepository;
use App\Repositories\TaxRatesGlossaryRepository;
use App\Repositories\TaxRatesRepository;
use App\Repositories\TopFiveRepository;
use App\Repositories\UserRepository;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use App\Repositories\InvestmentRankingStateUsRepository;
use App\Repositories\InvestmentRankingThresholdRepository;
use App\Repositories\ActivityLogRepository;
use DB;
use Excel;
use Illuminate\Support\Facades\Config;

class CmsService
{

    private $topFiveRepository;
    private $taxAlertRepository;
    private $cannabisBenchmarksUsRepository;
    private $cannibalizationRepository;
    private $bundleReportRepository;
    private $dataSetRepository;
    private $stateLegalizedRepository;
    private $report;
    private $user;
    private $saleProjectionsRepository;
    private $interactiveReport;
    private $taxRatesRepository;
    private $countryRepository;
    private $taxRatesGlossaryRepository;
    private $companyNewsRepository;
    private $investmentRankingStateUsRepository;
    private $investmentRankingThresholdRepository;
    private $activityLogRepository;

    /**
     * topFiveService constructor.
     * @param topFiveRepository $topFiveRepository
     * @param TaxAlertRepository $taxAlertRepository
     * @param CannabisBenchmarksUsRepository $cannabisBenchmarksUsRepository $cannabisBenchmarksUsRepository
     * @param ReportRepository $report
     * @param UserRepository $user
     * @param DataSetRepository $dataSetRepository
     * @param SaleProjectionsRepository $saleProjectionsRepository
     * @param CannibalizationRepository $cannibalizationRepository
     * @param BundleReoportRepository $bundleReportRepository
     * @param StateLegalizedRepository $stateLegalizedRepository
     */
    public function __construct(
        TopFiveRepository $topFiveRepository,
        TaxAlertRepository $taxAlertRepository,
        CannabisBenchmarksUsRepository $cannabisBenchmarksUsRepository,
        ReportRepository $report,
        UserRepository $user,
        DataSetRepository $dataSetRepository,
        SaleProjectionsRepository $saleProjectionsRepository,
        CannibalizationRepository $cannibalizationRepository,
        BundleReoportRepository $bundleReportRepository,
        StateLegalizedRepository $stateLegalizedRepository,
        InteractiveReportRepository $interactiveReport,
        TaxRatesRepository $taxRatesRepository,
        CountryRepository $countryRepository,
        TaxRatesGlossaryRepository $taxRatesGlossaryRepository,
        CompanyNewsRepository $companyNewsRepository,
        InvestmentRankingStateUsRepository $investmentRankingStateUsRepository,
        InvestmentRankingThresholdRepository $investmentRankingThresholdRepository,ActivityLogRepository $activityLogRepository
    )
    {

        $this->report = $report;
        $this->user = $user;
        $this->topFiveRepository = $topFiveRepository;
        $this->taxAlertRepository = $taxAlertRepository;
        $this->cannabisBenchmarksUsRepository = $cannabisBenchmarksUsRepository;
        $this->cannibalizationRepository = $cannibalizationRepository;
        $this->dataSetRepository = $dataSetRepository;
        $this->bundleReportRepository = $bundleReportRepository;
        $this->saleProjectionsRepository = $saleProjectionsRepository;
        $this->stateLegalizedRepository = $stateLegalizedRepository;
        $this->interactiveReport = $interactiveReport;
        $this->taxRatesRepository = $taxRatesRepository;
        $this->countryRepository = $countryRepository;
        $this->taxRatesGlossaryRepository = $taxRatesGlossaryRepository;
        $this->companyNewsRepository = $companyNewsRepository;
        $this->investmentRankingStateUsRepository = $investmentRankingStateUsRepository;
        $this->investmentRankingThresholdRepository = $investmentRankingThresholdRepository;
        $this->activityLogRepository = $activityLogRepository;
    }

    /**
     * get All caninbit Image details
     * @return mixed
     */
    public function getCaninBitImg()
    {

        $cannabits_doc = new \DOMDocument();
        $html = @file_get_contents('https://newfrontierdata.com/tag/cannabits/');
        libxml_use_internal_errors(TRUE);

        if (!empty($html)) {

            $cannabits_doc->loadHTML($html);
            libxml_clear_errors();
            $cannabits_xpath = new  \DOMXPath($cannabits_doc);
            $cannabits_row = $cannabits_xpath->query('//article[@id]');

            if ($cannabits_row->length > 0) {
                $count = 0;
                foreach ($cannabits_row as $row) {

                    $aHref = $row->getElementsByTagName("a")->item(0)->getAttribute('href');
                    $imgSrc = $row->getElementsByTagName("img")->item(0)->getAttribute('src');
                    $h2Title = $row->getElementsByTagName("h2")->item(0)->nodeValue;

                    foreach ($row->getElementsByTagName("div") as $div) {
                        if ($div->getAttribute('class') == 'post-content nz-clearfix') {
                            $textContent = trim($div->nodeValue);
                        }
                    }

                    $cannabits_list[] = array('imageUrl' => $imgSrc, 'articleUrl' => $aHref, 'headline' => $h2Title,
                        'fullStory' => $textContent, 'num' => $count);
                    $count += 1;
                }

                return ($cannabits_list);
            }

        }

    }

    /**
     *
     * get user details by email
     *
     * @param $email
     * @return mixed
     */
    public function getUserByEmail($email)
    {
        return $this->user->userByEmail($email);
    }

    /**
     *
     * get all reports
     *
     * @return mixed
     */
    public function getAllReports($request)
    {
        return $this->report->getAllReports($request);;
    }


    public function writeExcelToDb($dasetId, $readCsv, $tblName, $type)
    {
        $objDatasetFac = new  DatasetFactoryMethod();
        $path = base_path("public/" . $readCsv['fileName']);
        $tableColumn = DB::select(
            DB::raw('show columns from ' . $tblName)
        );
        unset($tableColumn[0]);
        $tableColumn = array_values($tableColumn);

        $lastElement = array_values(array_slice($tableColumn, -1))[0];
        if ($lastElement->Field == "updated_at") {
            array_pop($tableColumn);
            $lastElement = array_values(array_slice($tableColumn, -1))[0];
        }
        if ($lastElement->Field == "created_at") {
            array_pop($tableColumn);
            $lastElement = array_values(array_slice($tableColumn, -1))[0];
        }
        if ($lastElement->Field == "dataset_id") {
            array_pop($tableColumn);
            //$lastElement = array_values ( array_slice ( $tableColumn, -1 ) )[ 0 ];
        }

        $data = Excel::load($path, function ($reader) {
        })->get();

        $HeaderColumn = $data->first()->keys()->toArray();
        $datas = [];

        if (count($tableColumn) != count($HeaderColumn)) {

            //\File::delete(public_path($path));
            return ['msg' => 'There were uploded wrong csv'];
        }

        $insert = [];

        if (!empty($data) && $data->count()) {

            foreach ($data as $rows) {

                $tags = array_map(function ($rows) use ($objDatasetFac) {
                    return $objDatasetFac->makeDatasetRead($rows, 2);
                }, (array)$rows);

                $key = array_keys($tags);
                $insert[] = $tags[$key[1]];
            }

            if (!empty($insert)) {
                DB::table($tblName)->insert($insert);
                return true;
            }

        }

    }

    public function addUploadCsvDataSet($request)
    {

        $dataCsv = $request['datacsv'];
        $type = $request['type'];
        unset($request['datacsv']);
        $dataSetId = $this->dataSetRepository->saveDataSet($request);
        $uploadCsv_table = $this->dataSetRepository->uploadCsvDataSet($dataCsv, $request['type']);
        $tblName = '';
        if (empty($uploadCsv_table)) {

            return ['msg' => 'There is already uploded this csv'];
        } else {

            $tableName = $uploadCsv_table['tblName'];

            return $this->writeExcelToDb($dataSetId, $uploadCsv_table, $tableName, $type);
        }

    }

    /**
     * get all bundle report details
     * @return mixed
     */
    public function getBundleReportDetail($request)
    {
        return $this->bundleReportRepository->allBundleReportInfo($request);
    }

    public function getReportUrl($request)
    {
        return $this->report->getReportUrlById($request);
    }


    public function getAllInteractiveReports($searchType, $search, $segment, $ids)
    {
        return $this->interactiveReport->getAllReports($searchType, $search, $segment, $ids);
    }

    public function getAllReportName()
    {
        return $this->report->getAllReportsName();
    }

    public function getCountryInfo($request)
    {
        return $this->countryRepository->allCountryInfo($request);
    }

    public function getStateInfo($request)
    {
        return $this->countryRepository->allStateInfo($request);
    }

    public function getCountryPhoneCode($request)
    {
        $id = (!empty( $request[ 'id' ] )) ? ($request[ 'id' ]) : 0;
        return $this->countryRepository->countrPhoneCodeInfo($id);
    }

    public function getBundleNameList()
    {
        return $this->bundleReportRepository->allBundleName();
    }

    public function getPurchasedReports($reportList)
    {
        return $this->report->getPurchasedReports($reportList);
    }

    public function getPurchasedReportName($reportList)
    {
        return $this->report->getPurchasedReportName($reportList);
    }

    public function getPurchasedReportEssentail($reportList)
    {
        return $this->report->getPurchasedReportEssential($reportList);
    }

    public function getPriceZeroReportName($reportList)
    {
        return $this->report->getPriceZeroReportName($reportList);
    }

    public function getReportName($reportList)
    {
        return $this->report->getReportName($reportList);
    }

    public function essentailReportDetail($reportList)
    {
        return $this->report->getEssentailReportId($reportList);
    }

    public function getCompanyNewsInfo($request)
    {
        return $this->companyNewsRepository->allCompanyNewInfo($request);
    }

    public function getCopmanyNewDetailInfo($request)
    {
        return $this->companyNewsRepository->allNewsDetailInfo($request);
    }

    public function getAvailableReports($reportList)
    {
        return $this->report->getAvailableReports($reportList);
    }

    public function getReportNameList($request)
    {
        return $this->report->getReportNameList($request);
    }

    public function getReportsByCategory($search, $ids)
    {
        return $this->report->getReportsByCategory($search, $ids);
    }

    public function getAvailableBundle($list)
    {
        return $this->bundleReportRepository->getAvailableBundle($list);
    }

    public function getInteractiveReportSearch($search, $type)
    {
        return $this->interactiveReport->getInteractiveReportSearch($search, $type);
    }

    public function getAllReportsName($segment)
    {
        return $this->interactiveReport->getAllReportsName($segment);
    }

    public function getAllZeroPriceReportIds()
    {
        return $this->report->getAllZeroPriceReportIds();
    }

    public function createInvestmentRank($data)
    {
        set_time_limit(1000);
        ini_set('memory_limit', '-1');
        return $this->investmentRankingThresholdRepository->uploadExcel($data);
    }


    public function editThreshold($data)
    {
        return $this->investmentRankingThresholdRepository->editThreshold($data);
    }


    public function editInvestmentRank($request)
    {
        set_time_limit(1000);
        ini_set('memory_limit', '-1');
        $latest = (!empty($request['status'])) ? ($request['status']) : 0;
        $dataSetId = !empty($request['dataset_id']) ? $request['dataset_id'] : 0;
        if (isset($request['investment_ranking'])) {

            $image = $request['investment_ranking'];
            if (gettype($image) == 'object') {

                return $this->investmentRankingThresholdRepository->uploadEditExcel($request);
            } else {
                return $this->investmentRankingThresholdRepository->updateThreshold($dataSetId, $latest);
            }
        } else {
            return $this->investmentRankingThresholdRepository->updateThreshold($dataSetId, $latest);
        }
    }

    public function createActvityLog($data)
    {
        return $this->activityLogRepository->createActivityLog($data);
    }

    public function viewActvityLog($data)
    {
        return $this->activityLogRepository->allActivityLogInfo($data);
    }

    public function getReportsById($data)
    {
        return $this->report->getReportUrlById($data);
    }

}
