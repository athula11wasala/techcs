<?php

namespace App\Http\Controllers;

use App\Services\KeyWordService;
use App\Traits\KeyWordValidator;
use Illuminate\Http\Request;

class KeyWordController extends ApiController
{

    use KeyWordValidator;

    private $error = 'error';
    private $message = 'message';

    /**
     * @var UserService
     */
    private $keyWordService;

    /**
     * UsersController constructor.
     * @param UserService $userService
     */
    public function __construct(KeyWordService $keyWordService)
    {

        $this->keyWordService = $keyWordService;
    }


    public function addNewKeyWord(Request $request)
    {

        $validator = $this->keyWordValidate ( $request->all () );

        if ( $validator->fails () ) {

            return response ()->json ( ['error' => $validator->errors ()], 400 );
        }

        if ( $validator->passes () ) {

            $keyWordData = $this->keyWordService->createKeyWord ( $request );

            if ( $keyWordData ) {
                return response ()->json ( ['message' => __ ( 'messages.key_word_success' )], 200 );
            }

            return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

        }

    }

    public function editKeyWord(Request $request)
    {

        $validator = $this->keyWordValidate ( $request->all (), 'PUT' );

        if ( $validator->fails () ) {

            return response ()->json ( ['error' => $validator->errors ()], 400 );
        }

        if ( $validator->passes () ) {
            $keyWordData = $this->keyWordService->getUpdateKeyWord ( $request );

            if ( $keyWordData ) {
                return response ()->json ( ['message' => __ ( 'messages.key_word_update_success' )], 200 );
            }

            return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

        }

    }

    public function deleteKeyWord($id = null)
    {

        $validator = $this->keyWordValidate ( ['id' => $id], 'DELETE' );

        if ( $validator->fails () ) {

            return response ()->json ( ['error' => $validator->errors ()], 400 );
        }

        if ( $validator->passes () ) {
            $keyWordData = $this->keyWordService->deleteKeyWord ( ['id' => $id] );

            if ( $keyWordData ) {
                return response ()->json ( ['message' => __ ( 'messages.key_word_delete_success' )], 200 );
            }
            return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

        }

    }

    public function searchKeyWord(Request $request)
    {
        $keyWordData = $this->keyWordService->getAllKeyWordDetailPaginate ( $request );

        if ( $keyWordData ) {

            return response ()->json ( ['data' => $keyWordData], 200 );

        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function allKeyWord(Request $request)
    {
        $keyWordData = $this->keyWordService->getAllKeyWordDetail ( $request );

        if ( $keyWordData ) {

            return response ()->json ( ['keywords' => $keyWordData], 200 );

        }
        return response ()->json ( [$this->error => __ ( 'messages.un_processable_request' )], 400 );

    }


}

