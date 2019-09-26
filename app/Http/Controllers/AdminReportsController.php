<?php

namespace App\Http\Controllers;

use App\Services\CmsService;
use Illuminate\Http\Request;

class AdminReportsController extends ApiController
{


    private $cmsService;


    public function __construct(CmsService $cmsService)
    {

        $this->cmsService = $cmsService;
    }

    public function index(Request $request)
    {

    }

    public function userInteractionReport(Request $request)
    {
        $data = $this->cmsService->viewActvityLog($request->all());
        if ($data) {

            return response()->json(['data' => $data], 200);
        }
        return response()->json(['error' => __('messages.un_processable_request')], 400);
    }

}



