<?php

namespace App\Http\Controllers;

use App\Services\UserAcknowledgmentService;
use App\Traits\UserAcknowledgmentValidators;
use Illuminate\Http\Request;

class UserAcknowledgmentController extends ApiController
{

    use UserAcknowledgmentValidators;

    private $error = 'error';
    private $message = 'message';

    /**
     * @var UserService
     */
    private $userAcknowledgmentService;

    /**
     * UsersController constructor.
     * @param UserService $userService
     */
    public function __construct(UserAcknowledgmentService $userAcknowledgmentService)
    {

        $this->userAcknowledgmentService = $userAcknowledgmentService;
    }


    public function add(Request $request)
    {

        $validator = $this->acknowlwdgmentValidate ( $request->all () );

        if ( $validator->fails () ) {

            return response ()->json ( ['error' => $validator->errors ()], 400 );
        }

        if ( $validator->passes () ) {

            $companyData = $this->userAcknowledgmentService->createUserAcknowledgements ( $request );

            if ( $companyData ) {
                return response ()->json ( ['message' => __ ( 'messages.create_success' )], 200 );
            }

            return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

        }

    }


}

