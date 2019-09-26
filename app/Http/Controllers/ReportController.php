<?php

namespace App\Http\Controllers;

use App\Equio\Exceptions\EquioException;
use App\Equio\Helper;
use App\Http\Requests\SegmentReportRequest;
use App\Services\ChartsService;
use App\Services\CmsService;
use App\Services\DashBoardService;
use App\Services\ReportService;
use App\Traits\ReportValidator;
use App\Transformers\SegmentReportTransformer;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;


class ReportController extends ApiController
{

    use ReportValidator;
    private $cmsService;
    private $chartsService;
    private $dashBoardService;
    private $reportService;
    private $error;

    public function __construct(
        CmsService $cmsService,
        ChartsService $chartsService,
        DashBoardService $dashBoardService,
        ReportService $reportService

    )
    {
        $this->cmsService = $cmsService;
        $this->chartsService = $chartsService;
        $this->dashBoardService = $dashBoardService;
        $this->reportService = $reportService;

        $this->woocommerce = new Client(
            Config::get('custom_config.WOOCOMMERCE_API_URL'),
            Config::get('custom_config.WOOCOMMERCE_API_KEY'),
            Config::get('custom_config.WOOCOMMERCE_API_SECRET'),
            ['wp_api' => true, 'version' => 'wc/v1',]
        );
    }


    /**
     * @param $email
     * @return Collection
     */
    public function getOrderDetails($email)
    {
        try {
            $purchasedItem = collect();
            $results = $this->woocommerce->get(
                'orders',
                [
                    'search' => $email,
                    'per_page' => 50,
                    'status' => 'completed'
                ]
            );
            foreach ($results as $result) {
                foreach ($result->line_items as $lineItem) {
                    $purchasedItem->push($lineItem->product_id);
                }
            }
            $results = $this->woocommerce->get(
                'orders',
                [
                    'search' => $email,
                    'per_page' => 50,
                    'status' => 'processing'
                ]
            );
            foreach ($results as $result) {
                foreach ($result->line_items as $lineItem) {
                    $purchasedItem->push($lineItem->product_id);
                }
            }
            return $purchasedItem;

        } catch (HttpClientException $e) {
            \Log::info("==== ReportController->getOrderDetails ERROR 1 ", ['u' => json_encode($e->getMessage())]);
            throw new EquioException($e->getMessage());
        } catch (\Exception $e) {
            \Log::info("==== ReportController->getOrderDetails ERROR 2 ", ['u' => json_encode($e->getMessage())]);
            throw new EquioException($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getAvailableReports(Request $request)
    {
        \Log::info("==== ReportController->getAvailableReports ", ['u' => json_encode($request)]);
        $email = urldecode( $request->email);
        $user = $this->cmsService->getUserByEmail($email);
        $reportDataList = collect();
        $allReportsData = collect();
        $paginateData = collect();
        $searchByCategoryData = (!empty($request['searchByCategory'])) ? ($request['searchByCategory']) : null;
        try {
            $purchasedOrder = $this->getOrderDetails($email);
            $objHelper = new Helper();
            $subscription = $objHelper->userSubscription(
                $user->paid_subscription_start,
                $user->paid_subscription_end
            );
            if ($subscription && $user->subscription_level != Config::get('custom_config.PACKAGE_ESSENTIAL')) {
                $paginateData->put('showLib', false);
                $allReportsData = $this->reportService->searchAvailableReportData($request, null, $user);
                $reportDataList = $this->reportService->searchAvailableReportName(null,$searchByCategoryData);
            } elseif ($subscription && $user->subscription_level == Config::get('custom_config.PACKAGE_ESSENTIAL')) {
                $zeroPriceReport = $this->cmsService->getAllZeroPriceReportIds();
                if ($zeroPriceReport != null && $subscription) {
                    $purchasedOrder = $purchasedOrder->concat($zeroPriceReport);
                }
                $reportDataList = $this->reportService->searchAvailableReportName($purchasedOrder,$searchByCategoryData);
                $allReportsData = $this->reportService->searchAvailableReportData($request, $purchasedOrder, $user);
                $paginateData->put('showLib', true);
            } elseif (!$subscription && $user->reports_purchased == "y") {
                $paginateData->put('showLib', true);
                $reportDataList = $this->reportService->searchAvailableReportName($purchasedOrder,$searchByCategoryData);
                $allReportsData = $this->reportService->searchAvailableReportData($request, $purchasedOrder, $user);
            }
        } catch (EquioException $ex) {
            \Log::info("==== ReportController->getAvailableReports ERROR ", ['u' => json_encode($ex->getMessage())]);
            return $this->respondBadRequest("Error occurred while getting reports - " . $ex->getMessage());
        }
        $paginateData->put('reports', $allReportsData);
        $paginateData->put('reportNameList', $reportDataList);
        return $this->respond($paginateData);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPurchasedReports(Request $request)
    {

        $email =urldecode($request->email);
        $user = $this->cmsService->getUserByEmail($email);
        $reportDataList = collect();
        $allReportsData = collect();
        $paginateData = collect();
        try {
            $purchasedOrder = $this->getOrderDetails($email);
            $objHelper = new Helper();
            $subscription = $objHelper->userSubscription(
                $user->paid_subscription_start,
                $user->paid_subscription_end
            );

            if ($subscription && $user->subscription_level != Config::get('custom_config.PACKAGE_ESSENTIAL')) {

                // this varible is used for sending pruchase order getting exempt = 1 value(Premium/premium-plus/enterprise)
                $purchasedOrder_prum_enterprise =  $purchasedOrder;
                $allReportsData = $this->reportService->searchPurchasedReportData($request, null, $user,$purchasedOrder_prum_enterprise);
                $reportDataList = $this->reportService->searchPurchasedReportName(null,$purchasedOrder_prum_enterprise,$user->subscription_level);
            } elseif ($subscription && $user->subscription_level == Config::get('custom_config.PACKAGE_ESSENTIAL')) {

                $zeroPriceReport = $this->cmsService->getAllZeroPriceReportIds();
                if ($zeroPriceReport != null && $subscription) {
                    $purchasedOrder = $purchasedOrder->concat($zeroPriceReport);
                }
                $reportDataList = $this->reportService->searchPurchasedReportName($purchasedOrder);
                $allReportsData = $this->reportService->searchPurchasedReportData($request, $purchasedOrder, $user);
            } elseif (!$subscription && $user->reports_purchased == "y") {
                $reportDataList = $this->reportService->searchPurchasedReportName($purchasedOrder);
                $allReportsData = $this->reportService->searchPurchasedReportData($request, $purchasedOrder, $user);
            }
        } catch (EquioException $ex) {
            \Log::info("==== ReportController->getPurchasedReports ERROR ", ['u' => json_encode($ex->getMessage())]);
            return $this->respondBadRequest("Error occurred while getting reports - " . $ex->getMessage());
        }
        $paginateData->put('reports', $allReportsData);
        $paginateData->put('reportNameList', $reportDataList);
        return $this->respond($paginateData);
    }

    private function urlEncoderWithBase($url, $folder)
    {
        $fullUrl = Url('/') . Config::get('custom_config.REPORTS_STORAGE') . $folder . $url;
        return urldecode($fullUrl);
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function viewPdf(Request $request)
    {
        $reportId = $request->id;
        $type = $request->type;
        $reportData = $this->cmsService->getReportUrl($reportId);
        $pathToFile = null;

        if ($type == Config::get('custom_config.REPORT_TYPE.REPORTS_ENTERPRISES_PDF')) {
            $pathToFile = $this->urlEncoderWithBase(
                !empty($reportData->enterprise_pdf) ? $reportData->enterprise_pdf : '',
                Config::get('custom_config.REPORTS_ENTERPRISES_PDF')
            );
        }
        if ($type == Config::get('custom_config.REPORT_TYPE.REPORTS_FULL_PDF')
            || $type == Config::get('custom_config.REPORT_TYPE.REPORTS_FULL_PDF')
        ) {
            $pathToFile = $this->urlEncoderWithBase(
                !empty($reportData->full_pdf) ? $reportData->full_pdf : '',
                Config::get('custom_config.REPORTS_FULL_PDF')
            );
        }
        if ($type == Config::get('custom_config.REPORT_TYPE.REPORTS_SUMMERY_PDF')) {
            $pathToFile = $this->urlEncoderWithBase(
                !empty($reportData->summary_pdf) ? $reportData->summary_pdf : '',
                Config::get('custom_config.REPORTS_SUMMERY_PDF')
            );
        }
        return $this->respond($pathToFile);
    }


    /**
     * paginate reports
     *
     * @param $data
     * @param $page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateReport($data, $page)
    {
        $paginate = Config::get('custom_config.CHART_PAGINATE_PER_PAGE');;
        $offSet = ($page * $paginate) - $paginate;
        $itemsForCurrentPage = array_slice($data, $offSet, $paginate, true);
        $result = new \Illuminate\Pagination\LengthAwarePaginator($itemsForCurrentPage, count($data), $paginate, $page);
        return $result;

    }

    /**
     * GET /segment-reports
     * @param SegmentReportRequest $request
     * @return mixed
     */
    public function segmentReports(SegmentReportRequest $request)
    {
        $user = Auth::user();
        $reportViewType = $this->getReportViewableType($user);
        $segmentReportTransformer = new SegmentReportTransformer();
        try {
            $segmentReports = $segmentReportTransformer->transformCollection(
                $this->dashBoardService->getSegmentedReports($request, $user)
            );
            return $this->respond(['reports' => $segmentReports, 'userViewType' => $reportViewType]);
        } catch (EquioException $ex) {
            \Log::info("==== ReportController->segmentReports ERROR ", ['u' => json_encode($ex->getMessage())]);
            return $this->respondBadRequest("Error occurred while getting reports - " . $ex->getMessage());
        }
    }

    /**
     * GET /user-state-reports
     * @param Request $request
     * @return mixed
     */
    public function userStateReports(Request $request)
    {
        $user = Auth::user();
        $reportViewType = $this->getReportViewableType($user);
        $segmentReportTransformer = new SegmentReportTransformer();
        try {
            $segmentReports = $segmentReportTransformer->transformCollection(
                $this->dashBoardService->getUserStateReports($user)
            );
            return $this->respond(['reports' => $segmentReports, 'userViewType' => $reportViewType]);
        } catch (EquioException $ex) {
            \Log::info("==== ReportController->userStateReports ERROR ", ['u' => json_encode($ex->getMessage())]);
            return $this->respondBadRequest(
                "Error occurred while getting reports - " . $ex->getMessage(),
                ['error_code' => $ex->getCode()]
            );
        }
    }

    /*
     * @param Request $request
     * @return mixed
     */
    public function getInteractiveReports(Request $request)
    {
        $user = Auth::user();
        $page = $request->page ? $request->page : 1;
        $chartPage = $request->chartPage ? $request->chartPage : 1;
        $searchType = $request->searchType;
        $search = $request->search;
        $segment = $request->segment;
        $paginateData = collect();
        $data = collect();
        $chartList = collect();
        $paginatedReportId = null;

        //$intReportsData = $this->cmsService->getAllInteractiveReports($searchType, $search, $segment, null);
        try {
            $purchasedOrder = $this->getOrderDetails($user->email);
            $objHelper = new Helper();
            $subscription = $objHelper->userSubscription($user->paid_subscription_start, $user->paid_subscription_end);
            if ($subscription) {
                $intReportsData = $this->cmsService->getAllInteractiveReports($searchType, $search, $segment, null);
            } else {
                $intReportsData = collect();
            }
        } catch (EquioException $ex) {
            return $this->respondBadRequest("Error occurred while getting reports - " . $ex->getMessage());
        }

        if ($searchType != "null" && $page == 1) {
            if ($searchType == Config::get('custom_config.REPORT_SEARCH_BY_ID') && $search != 0) {
                $intReportsData = $this->cmsService->getAllInteractiveReports(
                    $searchType,
                    $search,
                    $segment,
                    null
                );
            } elseif ($searchType == Config::get('custom_config.REPORT_SEARCH_BY_NAME')) {
                $intReportsData = $this->cmsService->getAllInteractiveReports($searchType, $search, $segment, null);
            }
        } elseif ($page >= 1) {
            $intReportsDataPage = $this->cmsService->getAllInteractiveReports(
                3,
                $search,
                $segment,
                null
            );
            foreach ($intReportsDataPage as $intSingReportData) {
                $paginatedReportId = $intSingReportData->report_id;
            }
        }

        foreach ($intReportsData as $intReportData) {
            $itemData = collect();
            $reportData = collect();
            $chartList = null;
            $itemData->put('id', $intReportData->id);
            $itemData->put('name', $intReportData->report_name);
            if ($intReportData->cannaclip != null) {
                $itemData->put('cannaclip_thumbnail', Helper::getYouTubeThumbnil($intReportData->cannaclip));
                $itemData->put('cannaclip_video', Helper::getYouTubeURL($intReportData->cannaclip));
                $itemData->put('cannaclip_id', Helper::getYouTubeId($intReportData->cannaclip));
            } else {
                $itemData->put('cannaclip_thumbnail', null);
                $itemData->put('cannaclip_video', null);
                $itemData->put('cannaclip_id', null);
            }
            $itemData->put(
                'summary_pdf',
                $pathToFile = $this->urlEncoderWithBase(
                    $intReportData->summary_pdf,
                    Config::get('custom_config.REPORTS_SUMMERY_PDF')
                )
            );
            if ($intReportData->author_headshot) {
                $itemData->put(
                    'author_image', $intReportData->author_headshot);

            } else {
                $itemData->put('author_image', null);
            }
            $itemData->put(
                'cover',
                $this->urlEncoderWithBase(
                    $intReportData->cover,
                    Config::get('custom_config.REPORTS_COVER')
                )
            );
            $itemData->put(
                'interactive_cover',
                $this->urlEncoderWithBase(
                    $intReportData->cover_image,
                    Config::get('custom_config.INTERACTIVE_COVER')
                )
            );
            $itemData->put('author', $intReportData->author);
            $itemData->put('leader', $intReportData->leader);
            $itemData->put('research_analysis', $intReportData->analysts);
            $itemData->put('summary', $intReportData->summary);
            $itemData->put('editor', $intReportData->editor);
            $itemData->put('marketing', $intReportData->marketing);
            $itemData->put('report_id', $intReportData->report_id);

            if ($user->subscription_level == Config::get('custom_config.PACKAGE_ENTERPRISE')
                || $user->subscription_level == Config::get('custom_config.PACKAGE_PREMIUMPLUS')) {
                if ($page >= 1 && $paginatedReportId != null) {
                    $chartList = $this->chartsService->getChartListFromReportId($paginatedReportId);
                } else {
                    $chartList = $this->chartsService->getChartListFromReportId($intReportData->report_id);
                }
                $itemData->put('key_findings_text', $intReportData->key_findings_text);
                $itemData->put('key_findings_list', $intReportData->key_findings_list);
            } else {
                $itemData->put('key_findings_text', null);
                $itemData->put('key_findings_list', null);
            }
            $data->push($itemData);
        }


        if ($data != null) {
            $paginateData->put('reports', ($this->paginateInteractiveReport($data->toArray(), $page)));
        } else {
            $paginateData->put('reports', null);
        }
        $allReportNames = $this->cmsService->getAllReportsName($segment);

        $paginateData->put('reportNameList', $allReportNames);

        if ($chartList != null) {
            $paginateData->put('chart_list', ($this->paginateReport($chartList->toArray(), $chartPage)));
        } else {
            $paginateData->put('chart_list', null);
        }
        return $this->respond($paginateData);

    }

    /**
     * @param $data
     * @param $page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateInteractiveReport($data, $page)
    {
        $paginate = Config::get('custom_config.INTERACTIVE_PAGINATE_PER_PAGE');;
        $offSet = ($page * $paginate) - $paginate;
        $itemsForCurrentPage = array_slice($data, $offSet, $paginate, true);
        $result = new \Illuminate\Pagination\LengthAwarePaginator($itemsForCurrentPage, count($data), $paginate, $page);
        return $result;

    }

    public function videoUrlCreator($url, $type)
    {
        $result = null;
        if ($type == Config::get('custom_config.VIDEO_TYPE_YOUTUBE')) {
            $result = "http://www.youtube.com/embed/" . Helper::getYouTubeId($url);
        }
        return $result;
    }

    /**
     * Returns report viewable type based on user
     * @param $user
     * @return int
     */
    private function getReportViewableType($user)
    {
        $type = Config::get('custom_config.REPORT_TYPE.REPORTS_FULL_PDF');
        if ($user->subscription_level == Config::get('custom_config.PACKAGE_PREMIUMPLUS') || $user->subscription_level == Config::get('custom_config.PACKAGE_ENTERPRISE')) {
            $type = Config::get('custom_config.REPORT_TYPE.REPORTS_ENTERPRISES_PDF');
        } elseif ($user->subscription_level == Config::get('custom_config.PACKAGE_PREMIUM') || $user->subscription_level == Config::get('custom_config.PACKAGE_ESSENTIAL')) {
            $type = Config::get('custom_config.REPORT_TYPE.REPORTS_FULL_PDF');
        }
        return $type;
    }


    public function getAllReportsName(Request $request)
    {
        $allReportsName = $this->cmsService->getReportNameList($request);
        return $this->respond($allReportsName);
    }


    public function allReportInfo(Request $request)
    {
        $data = $this->dashBoardService->getReportInfo($request);

        if ($data) {

            return response()->json(['reports' => $data], 200);

        }
        return response()->json([$this->error => __('messages.un_processable_request')], 400);

    }

    public function addReports(Request $request)
    {
        $validator = $this->reportValidate($request->all());
        if ($validator->fails()) {

            $validateMessge = Helper::customErrorMsg($validator->messages());

            return response()->json(['error' => $validateMessge], 400);
        }
        if ($validator->passes()) {
            $reportData = $this->reportService->createReport($request->all());
            if ($reportData) {
                if (isset($reportData['success']) && isset($reportData['message'])) {
                    return response()->json(['error' => __($reportData['message'])], 400);
                }
                return response()->json(['message' => __('messages.report_add_success')], 200);
            }
            return response()->json(['error' => __('messages.un_processable_request')], 400);
        }
    }

    public function editReports(Request $request)
    {
        $validator = $this->reportValidate($request->all(), 'PUT');
        if ($validator->fails()) {
            $validateMessge = Helper::customErrorMsg($validator->messages());

            return response()->json(['error' => $validateMessge], 400);
        }
        if ($validator->passes()) {
            $reportData = $this->reportService->updateReport($request->all());
            if ($reportData) {
                if (isset($reportData['success']) && isset($reportData['message'])) {
                    return response()->json(['error' => __($reportData['message'])], 400);
                }
                return response()->json(['message' => __('messages.report_update_success')], 200);
            }
            return response()->json(['error' => __('messages.un_processable_request')], 400);
        }
    }

    public function updateReportExempt(Request $request)
    {
            $reportData = $this->reportService->updateReportExempt($request->all());
            if ($reportData) {
                return response()->json(['message' => __('messages.report_exempt_update_success')], 200);
            }
            return response()->json(['error' => __('messages.un_processable_request')], 400);

    }


    public function getReportRefernceData()
    {
        $objHelper = new Helper();
        return response()->json(['digital_copy_wooId' => $objHelper->wooComerceWodIdInfo('callbackWooDigital'),
            'hard_copy_wooId' => $objHelper->wooComerceWodIdInfo('callbackWooHardCopy'),
            'segmentInfo' => Helper::reportSegmentInfo(),
            'categoryInfo' => Helper::reportCategoryInfo(),
        ], 200);
    }

    public function listReport(Request $request)
    {
        $reportData = $this->reportService->getReortDetail($request);
        $creditUserData = $this->reportService->getCreditUserDetail();
        if ($reportData) {
            return response()->json(['data' => $reportData, 'segment' => Helper::reportSegmentInfo(), 'category' => Helper::reportCategoryInfo(),
                'author' => $creditUserData['author'], 'research' => $creditUserData['research'], 'editor' => $creditUserData['editor'],
                'markeingPR' => $creditUserData['markeing&PR']
            ], 200);
        }
        return response()->json(['data' => ['data' => $reportData], 'segment' => Helper::reportSegmentInfo(), 'category' => Helper::reportCategoryInfo(),
            'author' => $creditUserData['author'], 'research' => $creditUserData['research'], 'editor' => $creditUserData['editor'],
            'markeingPR' => $creditUserData['markeing&PR']
        ], 400);
    }


    public function getWoodIdPrice($WoodId = null)
    {
        $price = Helper::getWooCommerceRetivePrice($WoodId);

        if (isset($price)) {

            return response()->json(['prrice' => $price
            ], 200);

        }

        return response()->json(['prrice' => $price
        ], 400);
    }


    public function selectReportsList($id = null)
    {
        $reportData = $this->reportService->getSelectReortDetail($id);
        if ($reportData) {
            return response()->json(['data' => $reportData,
            ], 200);
        } else {
            return response()->json(['error' => __('messages.un_processable_request')], 400);;
        }
    }


    public function getReportContentData(Request $request)
    {
        $reportId = $request->id;
        $type = $request->type;
        $reportData = $this->cmsService->getReportUrl($reportId);
        $pathToFile = null;
        $user = Auth::user();
        if ($type != "null") {
            if ($type == Config::get('custom_config.REPORT_TYPE.REPORTS_ENTERPRISES_PDF')) {
                $pathToFile = $this->urlEncoderWithOutBase(
                    !empty($reportData->enterprise_pdf) ? $reportData->enterprise_pdf : '',
                    Config::get('custom_config.REPORTS_ENTERPRISES_PDF')
                );
            }
            if ($type == Config::get('custom_config.REPORT_TYPE.REPORTS_FULL_PDF')
                || $type == Config::get('custom_config.REPORT_TYPE.REPORTS_FULL_PDF')
            ) {
                $pathToFile = $this->urlEncoderWithOutBase(
                    !empty($reportData->full_pdf) ? $reportData->full_pdf : '',
                    Config::get('custom_config.REPORTS_FULL_PDF')
                );
            }
            if ($type == Config::get('custom_config.REPORT_TYPE.REPORTS_SUMMERY_PDF')) {
                $pathToFile = $this->urlEncoderWithOutBase(
                    !empty($reportData->summary_pdf) ? $reportData->summary_pdf : '',
                    Config::get('custom_config.REPORTS_SUMMERY_PDF')
                );
            }
        } else {
            if ($user->subscription_level == Config::get('custom_config.PACKAGE_PREMIUMPLUS') || $user->subscription_level == Config::get('custom_config.PACKAGE_ENTERPRISE')) {
                $pathToFile = $this->urlEncoderWithOutBase(
                    !empty($reportData->enterprise_pdf) ? $reportData->enterprise_pdf : '',
                    Config::get('custom_config.REPORTS_ENTERPRISES_PDF')
                );
            } elseif ($user->subscription_level == Config::get('custom_config.PACKAGE_PREMIUM') || $user->subscription_level == Config::get('custom_config.PACKAGE_ESSENTIAL')) {
                $pathToFile = $this->urlEncoderWithOutBase(
                    !empty($reportData->full_pdf) ? $reportData->full_pdf : '',
                    Config::get('custom_config.REPORTS_FULL_PDF')
                );
            }
        }

        $headers = array(
            'Content-Description: File Transfer',
            'Content-Type: application/octet-stream',
            'Content-Disposition: attachment; filename="' . $reportData->name . '"',
        );
        return response()->download("$pathToFile", $reportData->name, $headers);
    }


    private function urlEncoderWithOutBase($url, $folder)
    {
        $fullUrl = Config::get('custom_config.REPORTS_STORAGE_NEW') . $folder . $url;
        return $fullUrl;
    }
}








