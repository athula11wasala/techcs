<?php

namespace App\Http\Controllers;


use App\Services\MapService;
use App\Traits\CompanyProfileValidators;
use Illuminate\Http\Request;

class StateLegalizedController extends ApiController
{

    use CompanyProfileValidators;

    /**
     * @var UserService
     */

    private $mapService;

    /**
     * UsersController constructor.
     * @param UserService $userService
     */
    public function __construct(MapService $mapService)
    {

        $this->mapService = $mapService;
    }

    public function index(Request $request)
    {
        $data = $this->mapService->getStateLegalzedInfo ( $request );

        if ( $data ) {

            return response ()->json ( ['data' => $data], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }


    public function StateLegalizedInfo(Request $request)
    {
        $type = (!empty( $request[ 'type' ] )) ? ($request[ 'type' ]) : '';

        $data = $this->mapService->getStateLegalzedDetails ( $type );

        if ( $data ) {

            return response ()->json ( ['data' => $data], 200 );
        }
       return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }

}
