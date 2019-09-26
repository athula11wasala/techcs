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

class InsightDailyUsService
{

    private $topFiveRepository;


    /**
     * topFiveService constructor.
     */
    public function __construct(TopFiveRepository $topFiveRepository)

    {
        $this->topFiveRepository = $topFiveRepository;

    }

    public function getAllInsightDetail($request)
    {
        return $this->topFiveRepository->getAllInsightDaily($request->all ());
    }

    public function getCategoryWithImg()
    {
        return $this->topFiveRepository->getTopicCategoryWithImages();
    }

    public function createInsightDaily($dataArray)
    {
        return $this->topFiveRepository->saveInsightDaily($dataArray);
    }

    public function getUpdateInsightDaily($dataArray)
    {
        return $this->topFiveRepository->updateInsightDaily($dataArray);
    }

    public function getInsightDailyById($id)
    {
        return $this->topFiveRepository->insgihtDailyInfoById($id);
    }

    public function deleteInsightDaily($id)
    {
        return $this->topFiveRepository->deleteInsightDaily($id);
    }

}


