<?php

namespace App\Http\Controllers;

use App\Models\FeatureAlert;
use App\Services\FeatureAlertService;
use App\Traits\FeatureAlertValidators;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Equio\Helper;

class FeatureAlertController extends ApiController
{
    use FeatureAlertValidators;

    private $error = 'error';
    private $message = 'message';

    /**
     * @var FeatureAlertService
     */
    private $featureAlertService;


    /**
     * FeatureAlertController constructor.
     * @param FeatureAlertService $featureAlertService
     */
    public function __construct(FeatureAlertService $featureAlertService)
    {

        $this->featureAlertService = $featureAlertService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $alertData = $this->featureAlertService->getAll ( $request->all () );

        return $this->respond ( $alertData );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->featureAlertValidate ( $request->all (), 'POST' );

        if ( $validator->fails () ) {

            $validateMessge  =   Helper::customErrorMsg ($validator->messages ());

            return response ()->json ( ['error' => $validateMessge], 400 );
        }

        if ( $validator->passes () ) {

            $alertData = $this->featureAlertService->createAlert ( $request->all () );

            if ( $alertData ) {
                return response ()->json ( ['message' => __ ( 'messages.feature_alert_add_success' )], 200 );
            }

            return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

        }
    }


    public function showFeature($id = null)
    {

        $featureData = $this->featureAlertService->getFeatureById ( $id );
        if ( $featureData ) {

            return response ()->json ( ['data' => $featureData], 200 );
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }


    public function editFeatureStatus(Request $request)
    {

        $validator = $this->featureAlertValidate ( $request->all (), 'changeStatus' );

        if ( $validator->fails () ) {

            $validateMessge  =   Helper::customErrorMsg ($validator->messages ());

            return response ()->json ( ['error' => $validateMessge], 400 );
        }

        if ( $validator->passes () ) {
            $featureData = $this->featureAlertService->getUpdateStatus ( $request );

            if ( $featureData ) {
                return response ()->json ( ['message' => __ ( 'messages.update_success' )], 200 );
            }

            return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

        }

    }


    public function editFeature(Request $request)
    {

        $validator = $this->featureAlertValidate ( $request->all (), 'PUT' );

        if ( $validator->fails () ) {

            $validateMessge  =   Helper::customErrorMsg ($validator->messages ());

            return response ()->json ( ['error' => $validateMessge], 400 );
        }
        
        if ( $validator->passes () ) {
            $featureData = $this->featureAlertService->getUpdateFeature ( $request );

            if ( $featureData ) {
                return response ()->json ( ['message' => __ ( 'messages.update_success' )], 200 );
            }

            return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

        }

    }

    public function checkFeatureName(Request $request)
    {
        $featureExitData = $this->featureAlertService->getCheckFeatureTitle ( $request->all () );

        if ( $featureExitData ) {

            if ( ($featureExitData['success']) == 'fail')  {
                return response ()->json ( ['valid' => __ ( false)], 200 );
            }
            else {
                return response ()->json ( ['valid' => __ ( true)], 200 );
            }
        }

        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\FeatureAlert $featureAlert
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FeatureAlert $featureAlert)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FeatureAlert $featureAlert
     * @return \Illuminate\Http\Response
     */
    public function destroy(FeatureAlert $featureAlert)
    {
        //
    }

    /**
     * get all notification for display dashboard alert
     * @return mixed
     */
    public function getNotificationAlert()
    {

        $user = Auth::user ()->getAuthIdentifier ();

        $alertData = $this->featureAlertService->getAllNotification ( $user );

        return $this->respond ( $alertData );
    }

    /**
     * @param $data
     * @param array $headers
     * @return mixed
     */
    public function respond($data, $headers = [])
    {
        return response ()->json ( $data, $this->getStatusCode (), $headers );
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
