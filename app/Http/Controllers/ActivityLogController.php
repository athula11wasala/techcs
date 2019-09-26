<?php

namespace App\Http\Controllers;

use App\Services\CmsService;
use Illuminate\Http\Request;
use App\Traits\ActivityLogValidator;
use App\Equio\Helper;

class ActivityLogController extends ApiController
{

    use ActivityLogValidator;
    private $cmsService;


    public function __construct(CmsService $cmsService)
    {

        $this->cmsService = $cmsService;
    }


    public function index(Request $request)
    {

    }

    public function storeUserActivity(Request $request)
    {
        $validator = $this->activityLogValidate($request->all());
        if ($validator->fails()) {

            $validateMessge = Helper::customErrorMsg($validator->messages());

            return response()->json(['error' => $validateMessge], 400);
        }
        if ($validator->passes()) {
            $data = $this->cmsService->createActvityLog($request->all());

            if (!empty($data)) {

                return response()->json(['message' => __('messages.activty_log_add_success')], 200);
            }
            return response()->json(['error' => __('messages.un_processable_request')], 400);
        }


    }


}



