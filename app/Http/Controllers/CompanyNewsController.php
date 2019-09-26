<?php

namespace App\Http\Controllers;

use App\Services\CmsService;
use Illuminate\Http\Request;

class CompanyNewsController extends ApiController
{



    /**
     * @var CmsService
     */
    private $cmsService;

    /**
     * UsersController constructor.
     * @param CmsService $cmsService
     */
    public function __construct(CmsService $cmsService)
    {

        $this->cmsService = $cmsService;
    }


    public function index(Request $request)
    {

        $companyNewsInfo = $this->cmsService->getCompanyNewsInfo ($request->all());

        if ( $companyNewsInfo ) {
            return response ()->json ( ['data' => $companyNewsInfo,
            ], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function detailInfo(Request $request)
    {

        $companyNewsDetailInfo = $this->cmsService->getCopmanyNewDetailInfo ($request->all());

        if ( $companyNewsDetailInfo ) {
            return response ()->json ( ['data' => $companyNewsDetailInfo,
            ], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }


}



