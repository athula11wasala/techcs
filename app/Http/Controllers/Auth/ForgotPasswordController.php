<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\PasswordResetsRepository;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    private $userService, $password_reset;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserService $service, PasswordResetsRepository $passwordResetsRepository)
    {
        $this->middleware ( 'guest' );
        $this->userService = $service;
        $this->password_reset = $passwordResetsRepository;
    }

    public function getResetToken(Request $request)
    {
        $user = $this->userService->getBasicInfoByEmail ( $request[ 'email' ] );
        \Log::info ( "==== fetch user email " . $request[ 'email' ] );
        if ( !is_null ( $user ) ) {
            $token = $this->randomConfirmationCode ();
            $data = ['email' => $request[ 'email' ], 'token' => $token, 'created_at' => Carbon::now ()];
            $this->password_reset->create ( $data );
            $this->sendResetToken ( $user, $token );
            return response ()->json ( ['message' => __ ( 'messages.sent_forgot_password_email' )], 200 );
        } else {
            return response ()->json ( ['error' => __ ( 'messages.user_does_not_exists' )], 400 );
        }
    }

    private function randomConfirmationCode()
    {

        $id = str_random ( 30 );
        $validator = \Validator::make ( ['id' => $id], ['id' => 'unique:password_resets,token'] );
        if ( $validator->fails () ) {
            $this->randomConfirmationCode ();
        }

        return $id;
    }

    private function sendResetToken($user, $token)
    {
        $name = $user->first_name . ' ' . $user->last_name;
        $data = ['from' => Config::get ( 'custom_config.from_email' ), 'system' => Config::get ( 'custom_config.system_email_send' )];
        $subject = Lang::get ( 'email_subjects.forgot_password' );
        Mail::send ( 'emails.forotPassword', array('name' => $name, 'confirmationCode' => $token),
            function ($message) use ($user, $name, $subject, $data) {
                $message->from ( $data[ 'from' ], $data[ 'system' ] );
                $message->to ( $user->email, $name )->subject ( $subject );

            } );
    }

    public function verifyResetToken(Request $request)
    {
        $result = $this->password_reset->getToken ( $request[ 'token' ] );

        if ( count ( (array)$result ) > 0 ) {
            $this->userService->getUpdatePassword ( $result->email, $request->password );
            return response ()->json ( ['message' => __ ( 'messages.password_reset_successful' ), 'email' => $result->email], 200 );
        } else {
            return response ()->json ( ['error' => __ ( 'messages.token_does_not_exist' )], 400 );
        }
    }
}
