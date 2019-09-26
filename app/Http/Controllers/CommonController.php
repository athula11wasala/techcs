<?php

namespace App\Http\Controllers;

use App\Services\ChartsService;
use App\Services\CmsService;
use App\Services\CompanyProfilesService;
use App\Services\FeatureAlertService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class CommonController extends ApiController
{
    private $cmsService;
    private $chartService;
    private $reportService;
    private $profileService;
    private $newFeatureService;


    public function __construct(CmsService $cmsService, ChartsService $chartService, ReportService $reportService, CompanyProfilesService $profileService, FeatureAlertService $newFeatureService)
    {
        $this->cmsService = $cmsService;
        $this->chartService = $chartService;
        $this->reportService = $reportService;
        $this->profileService = $profileService;
        $this->newFeatureService = $newFeatureService;
    }


    public function getBlobData(Request $request)
    {
        $objectId = $request->id;
        $type = $request->type;
        $pathToFile = null;
        $object = null;
        switch ($type) {
            case 1:
                $object = $this->chartService->getChartById($objectId);
                $pathToFile = Config::get('custom_config.CHARTS_STORAGE_NEW') . $object->cover;
                break;
            case 2:
                $object = $this->cmsService->getReportsById($objectId);
                $pathToFile = Config::get('custom_config.REPORTS_STORAGE_NEW') . 'cover/' . $object->cover;
                break;
            case 3:
                $pathToFile;
                break;
            case 4:
                $object = $this->profileService->getComapnyById($objectId);
                $pathToFile = $pathToFile = Config::get('custom_config.PROFILES_STORAGE_NEW') . $object->cover;
                break;
            case 5:
                $object = $this->newFeatureService->getFeatureById($objectId);
                $pathToFile = $pathToFile = Config::get('custom_config.alert_image') . $object->cover;
                break;
            default:
                $pathToFile;
        }


        $headers = array(
            'Content-Description: File Transfer',
            'Content-Type: application/octet-stream',
            'Content-Disposition: attachment; filename="' . $object->name . '"',
        );
        return response()->download("$pathToFile", $object->name, $headers);
    }

}



