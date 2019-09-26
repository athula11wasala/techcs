<?php

namespace App\Http\Controllers;


use App\Services\MapService;
use Illuminate\Http\Request;

class CannibalizationController extends ApiController
{

/**
     * @var mapService
     */
    private $mapService;

    /**
     * CmsService constructor.
     */
    public function __construct(MapService $mapService)
    {

        $this->mapService = $mapService;
    }

    public function index(Request $request)
    {
        $canibilization = $this->mapService->getCannibalizationDetailsByState($request->all());

        if ($canibilization) {
            return response()->json(['data' => $canibilization], 200);
        }

        return response()->json(['error' => __('messages.un_processable_request')], 400);

    }

}
