<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Testing\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exceptions\CustomException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($exception instanceof \League\OAuth2\Server\Exception\OAuthServerException && $exception->getCode() == 9) {
            \Log::debug('The resource owner or authorization server denied the request');
            return;
        }
        
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {

        \Log::info($request);
        \Log::info(' =================================== ');
        //$class = get_class($exception);
        if ($request->isJson()) {

            // This will replace our 404 response with a JSON response.
            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'error' => 'Resource not found'
                ], 404);
            }
        }
        if ($exception instanceof \Illuminate\Http\Exceptions\PostTooLargeException) {
            //  return response()->view('errors.posttoolarge');
            return response()->json(['error' => __('messages.un_processable_request')], 400);
        }



        return parent::render($request, $exception);
    }
}
