<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfiles;
use App\Services\CmsService;
use App\Services\DashBoardService;
use Illuminate\Http\Request;

class CannabisBenchmarksUsController extends ApiController
{

    /**
     * @var CannabisBenchmarksUs
     */
    private $cmsService;
    private  $dashBoardService;

    /**
     * CmsService constructor.
     */
    public function __construct(CmsService $cmsService,DashBoardService $dashBoardService )
    {

        $this->cmsService = $cmsService;
        $this->dashBoardService = $dashBoardService;
    }

    public function index(Request $request)
    {
        $caninBenMenchMark = $this->dashBoardService->getAllCannabisBenchmarks ( $request->all () );

        if ( $caninBenMenchMark ) {
            return response ()->json ( ['data' => $caninBenMenchMark], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function priceCopmare(Request $request)
    {

        $caninBenMenchMark = $this->dashBoardService->ComparePriceCaninBenMenchMark ( $request->all () );
        if ( $caninBenMenchMark ) {
            return $this->respond ( $caninBenMenchMark );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function showImages()
    {

        $caninBitImages = $this->cmsService->getCaninBitImg ();

        if ( $caninBitImages ) {
            return response ()->json ( ['data' => $caninBitImages], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );


    }


}

