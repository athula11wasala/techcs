<?php

namespace App\Http\Controllers;

use App\Services\CmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\Config;
use App\Equio\Exceptions\EquioException;


class BundleController extends ApiController
{

    /**
     * @var chartsService
     */
    private $cmsService;

    /**
     * ChartsController constructor.
     * @param ChartsService $chartsService
     */
    public function __construct(CmsService $chartsService)
    {
        $this->chartsService = $chartsService;
        $this->woocommerce = new Client(Config::get('custom_config.WOOCOMMERCE_API_URL'), Config::get('custom_config.WOOCOMMERCE_API_KEY'), Config::get('custom_config.WOOCOMMERCE_API_SECRET'), ['wp_api' => true, 'version' => 'wc/v1',]);
        $this->cmsService = $chartsService;
    }

    public function getOrderDetails($email)
    {
        try {
            $results = $this->woocommerce->get('orders', ['search' => $email, 'per_page' => 50, 'status' => 'completed']);
            $purchasedItem = collect();
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

    public function index(Request $request)
    {
        $user = Auth::user();
        $purchasedOrder = $this->getOrderDetails($user->email);
        $bundleReportData = $this->cmsService->getAvailableBundle($purchasedOrder->toArray());
        $bundleNameList = $this->cmsService->getBundleNameList();
        $paginateData = collect();
        $paginateData->put('bundles', $bundleReportData);
        $paginateData->put('bundleNameList', $bundleNameList);
        return $this->respond($paginateData);
    }


}


