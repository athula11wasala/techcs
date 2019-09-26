<?php

namespace App\Services;

use App\Repositories\BundleReoportRepository;
use App\Repositories\CannabisBenchmarksUsRepository;
use App\Repositories\CannibalizationRepository;
use App\Repositories\CompanyNewsRepository;
use App\Repositories\CountryRepository;
use App\Repositories\Criteria\Users\OrderByCreated;
use App\Repositories\DataSetRepository;
use App\Repositories\InteractiveReportRepository;
use App\Repositories\QualifyConditionRepository;
use App\Repositories\ReportRepository;
use App\Repositories\SaleProjectionsRepository;
use App\Repositories\StateLegalizedRepository;
use App\Repositories\TaxAlertRepository;
use App\Repositories\TaxRatesGlossaryRepository;
use App\Repositories\InvestmentRankingThresholdRepository;
use App\Repositories\InvestmentRankingStateUsRepository;
use App\Repositories\TaxRatesRepository;
use App\Repositories\TopFiveRepository;
use App\Repositories\UserRepository;

use DB;
use Excel;


class MapService
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
    private $qualifyConditionRepository;
    private $investmentRankThresholdRepository;
    private $investmentRankStatUsRepository;

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
        QualifyConditionRepository $qualifyConditionRepository,
        InvestmentRankingThresholdRepository $investmentRankThresholdRepository,
        InvestmentRankingStateUsRepository $investmentRankStatUsRepository
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
        $this->qualifyConditionRepository = $qualifyConditionRepository;
        $this->investmentRankThresholdRepository = $investmentRankThresholdRepository;
        $this->investmentRankStatUsRepository = $investmentRankStatUsRepository;
    }

    public function getStateLegalzedDetails($type)
    {
        return $this->stateLegalizedRepository->getStateLegalziedDetails ( $type );
    }

    public function getStateLegalzedInfo($request)
    {
        return $this->stateLegalizedRepository->getStateLegalziedInfo ( $request );
    }

    public function getQualifyConditionInfo($request)
    {
        return $this->qualifyConditionRepository->qalifyConditionInfoByState ( $request );
    }

    public function getFilterQualifyCondition($request)
    {
        return $this->qualifyConditionRepository->filterQalifyConditionInfo ( $request );
    }

    public function getCannibalizationDetailsByState($request)
    {
        return $this->cannibalizationRepository->showDetailsByState ( $request );
    }

    public function getTaxRatesDetails()
    {
        return $this->taxRatesRepository->getTaxRates ();
    }

    public function getCountyDetails($state)
    {
        return $this->taxRatesRepository->getCountyInfo ( $state );
    }

    public function getCounty($state)
    {
        return $this->taxRatesRepository->getCounty ( $state );
    }

    public function getCityDetails($state, $county)
    {
        return $this->taxRatesRepository->getCity ( $state, $county );
    }

    public function stateLevelDetails($state)
    {
        return $this->taxRatesRepository->stateLevel ( $state );
    }

    public function countyLevelDetails($state, $county)
    {
        return $this->taxRatesRepository->countyLevel ( $state, $county );
    }

    public function getTaxRatesGlossary()
    {
        return $this->taxRatesGlossaryRepository->getTaxRatesGlossaryInfo ();
    }

    public function getTaxGlossary($state)
    {
        return $this->taxRatesGlossaryRepository->getTaxGlossaryInfo ( $state );
    }

    public function getInvestmentRankDetails()
    {
        return $this->investmentRankStatUsRepository->getInvestmentRankdDetails ();
    }

    public function sortInvestmentRankByCode()
    {
        return $this->investmentRankStatUsRepository->sortInvestmentRankdByCode ();
    }

    public function getInvestmentRankByDataSet($request)
    {
        $dataSetid = (!empty($request['dataset_id'])) ? ($request['dataset_id']) : '';
        return $this->investmentRankStatUsRepository->getInvestmentRankByDataSet ($dataSetid);
    }


    public function getInvestmentRankDataSet($request)
    {
        return   $this->dataSetRepository->getDataset($request);
    }

    public function getAllInvestmentRankDetails($request)
    {
        return $this->investmentRankStatUsRepository->getAllInvestmentRankdDetails ($request);
    }

    public function getAllThresholdDetails($request)
    {
        return $this->investmentRankThresholdRepository->getAllThresholdDetails ($request);
    }


}
