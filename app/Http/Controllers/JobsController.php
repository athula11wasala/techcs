<?php

namespace App\Http\Controllers;

use App\Services\JobsService;
use App\Services\MapService;
use Illuminate\Http\Request;

class JobsController extends ApiController
{

    /**
     * @var JobsService
     */
    private $jobsService;
    private $mapService;

    /**
     * JobsController constructor.
     * @param JobsService $jobsService
     */
    public function __construct(JobsService $jobsService,MapService $mapService)
    {
        $this->jobsService = $jobsService;
        $this->mapService = $mapService;
    }


    public function index(Request $request)
    {
        $jobsData = $this->jobsService->getAllJob($request->all());

        if ( $jobsData ) {
            return $this->respond($jobsData);
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function showMap(Request $request)
    {
        $mapData = $this->jobsService->getMapInfo($request->all());

        if ( $mapData ) {
            return $this->respond($mapData);
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function showMapDetails(Request $request)
    {
        $mapData = $this->jobsService->getMapDetailByState($request->all());

        if ( $mapData ) {
            return $this->respond($mapData);
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function getQualifyCondition(Request $request) {

        $data = $this->mapService->getQualifyConditionInfo($request->all());

        if ( $data ) {
            return $this->respond($data);
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function filterQualifyCondition(Request $request) {

        $data = $this->mapService->getFilterQualifyCondition($request->all());

        if ( $data ) {
            return $this->respond($data);
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
   }

}


