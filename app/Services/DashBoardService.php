<?php

namespace App\Services;

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
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use App\Equio\Helper;

class DashBoardService {

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
        CompanyNewsRepository $companyNewsRepository
    ) {

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
    }

    public function getTaxAlerts(Request $request) {
        $perPageCount = env('PAGINATE_PER_PAGE', 15);

        if ($request->perPage && $request->perPage >= 10) {

            $perPageCount = $request->perPage;
        }

        $orderColumn = 'date_meeting';
        $orderDesc = true;
        $filterArray = [];
        if ($request->state) {
            $filterArray = ['state' => $request->state];
        }
        \Log::info("filter array :", ['f' => $filterArray]);

        return $this->taxAlertRepository->getTaxAlerts($perPageCount, $orderColumn, $orderDesc, $filterArray);
    }

    /**
     * Returns all reports based on filters
     * @param $request
     * @return mixed
     * @throws EquioException
     */
    public function getSegmentedReports($request, $user) {
        $whereFilters = [];
        $whereFilters[] = ['segment', '=', $request->get('segmentType')];
        $wooCommerceIds = [];
        try {
            //if ($user->subscription_level == Config::get('custom_config.PACKAGE_ESSENTIAL')) {
            $wooCommerceIds = $this->getWooCommerceOrderIdsByEmail($user->email);
            $zeroReportData = $this->report->getAllSegmentZeroPriceReportIds($request->get('segmentType'));
            $objHelper = new Helper();
            $subscription = $objHelper->userSubscription(
                $user->paid_subscription_start,
                $user->paid_subscription_end
            );
            if ($zeroReportData != null && $subscription) {
                foreach ($zeroReportData as $zeroReport) {
                    $wooCommerceIds->push($zeroReport);
                }
            }
            // $whereFilters [] = function ($query) use ($wooCommerceIds) {
            //     $query->whereIn('reports.woo_id', $wooCommerceIds);
            // };

            //  }
            return $this->report->getAvailbleReports($request->get('segmentType'), true, $wooCommerceIds, $user->subscription_level);
            //return $this->report->getReports($whereFilters);
        } catch (EquioException $ex) {
            throw new EquioException($ex->getMessage());
        }
    }

    /**
     * Returns all reports based on user state
     * @return mixed
     * @throws EquioException
     */
    public function getUserStateReports($user) {
        $userObject = $this->user->find($user->id);
        if (!$userObject) {
            throw new EquioException("User profile not found");
        }
        $profile = $userObject->profile;
        $state_id = '';

        if (!empty($profile->state)) {

            $state_id = DB::table("states")->where("name", $profile->state)->select("id")->first();

        } else {

            if (!empty($profile->state_id)) {

                $state_id = $profile->state_id;
            }

        }
        $whereFilters = [];
        //  $whereFilters[] = ['state_id', '=', $profile->state];
        $whereFilters[] = ['state_id', '=', $state_id];

        if (empty($state_id)) {
            throw new EquioException("No state provided for user profile", 1);
        }
        try {
            if ($user->subscription_level == Config::get('custom_config.PACKAGE_ESSENTIAL')) {
                $wooCommerceIds = $this->getWooCommerceOrderIdsByEmail($user->email);
                $zeroReportData = $this->report->getAllZeroPriceReportIds();
                $objHelper = new Helper();
                $subscription = $objHelper->userSubscription(
                    $user->paid_subscription_start,
                    $user->paid_subscription_end
                );
                if ($zeroReportData != null && $subscription) {
                    foreach ($zeroReportData as $zeroReport) {
                        $wooCommerceIds->push($zeroReport);
                    }
                }
                $whereFilters [] = function ($query) use ($wooCommerceIds) {
                    $query->whereIn('reports.woo_id', $wooCommerceIds);
                };
            }
            $reports = $this->report->getReports($whereFilters);
            if ($user->subscription_level == Config::get('custom_config.PACKAGE_ESSENTIAL') && !count($reports)) {
                throw new EquioException("You have not purchased any reports", 2);
            } elseif ($user->subscription_level != Config::get('custom_config.PACKAGE_ESSENTIAL') && !count($reports)) {
                throw new EquioException("No available reports for your state", 3);
            }
            return $reports;
        } catch (EquioException $ex) {
            throw new EquioException($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Returns woo commerce order ids based on user email
     * @param $email
     * @return \Illuminate\Support\Collection
     * @throws EquioException
     */
    public function getWooCommerceOrderIdsByEmail($email) {
        $purchasedItem = collect();
        try {
            $wooCommerceApi = new Client(
                Config::get('custom_config.WOOCOMMERCE_API_URL'),
                Config::get('custom_config.WOOCOMMERCE_API_KEY'),
                Config::get('custom_config.WOOCOMMERCE_API_SECRET'),
                [
                    'wp_api' => true,
                    'version' => 'wc/v1'
                ]
            );
            $results = $wooCommerceApi->get('orders', ['search' => $email, 'per_page' => 50, 'status' => 'completed']);
            foreach ($results as $result) {
                foreach ($result->line_items as $lineItem) {
                    $purchasedItem->push($lineItem->product_id);
                }
            }
            $results = $wooCommerceApi->get('orders', ['search' => $email, 'per_page' => 50, 'status' => 'processing']);

            foreach ($results as $result) {
                foreach ($result->line_items as $lineItem) {
                    $purchasedItem->push($lineItem->product_id);
                }
            }

            return $purchasedItem;
        } catch (HttpClientException $e) {
            throw new EquioException($e->getMessage());
        } catch (\Exception $e) {
            throw new EquioException($e->getMessage());
        }
    }

    public function getSaleProjectionDetails($request) {
        return $this->saleProjectionsRepository->allSaleProjectionDetails($request);
    }

    public function getLatestNews() {
        return $this->topFiveRepository->getAllNews();
    }

    public function getAllCannabisBenchmarks($request) {
        return $this->cannabisBenchmarksUsRepository->showGraphInfo($request);

    }

    public function ComparePriceCaninBenMenchMark($request) {
        return $this->cannabisBenchmarksUsRepository->showPriceInfo($request);

    }

    public function getTopFive() {
        return $this->topFiveRepository->getTopfive();
    }

    public function getAllSearchedNews($request) {
        return $this->topFiveRepository->allNewsSearch($request);
    }

    public function getReportInfo($request) {
        return $this->report->reportInfo($request->all());
    }

}
