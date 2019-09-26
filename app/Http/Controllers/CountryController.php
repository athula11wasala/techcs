<?php

namespace App\Http\Controllers;

use App\Services\CmsService;
use Illuminate\Http\Request;

class CountryController extends ApiController
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

        $countryStateInfo = $this->cmsService->getCountryInfo ($request->all());

        if ( $countryStateInfo ) {
            return response ()->json ( ['data' => $countryStateInfo,
            ], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function stateInfo(Request $request)
    {

        $countryStateInfo = $this->cmsService->getStateInfo ($request->all());

        if ( $countryStateInfo ) {
            return response ()->json ( ['data' => $countryStateInfo,
            ], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    public function phoneCodeInfo(Request $request)
    {
        $phoneCodeInfo = $this->cmsService->getCountryPhoneCode ($request->all());
        if ( $phoneCodeInfo ) {
            return response ()->json ( ['data' => $phoneCodeInfo,
            ], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }


}



