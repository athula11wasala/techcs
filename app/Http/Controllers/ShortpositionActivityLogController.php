<?php

namespace App\Http\Controllers;

use App\Services\ShortpositionActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Equio\Helper;
use Illuminate\Support\Facades\Auth;
use App\Services\PaymentService;

class ShortpositionActivityLogController extends ApiController {

    private $shortpositionActivityLogService;
    private $paymentService;
    private $error = 'error';
    private $message = 'message';

    /**
     * ShortpositionActivityLogController constructor.
     * @param ShortpositionActivityLogService $shortpositionActivityLogService
     * @param PaymentService $paymentService
     */
    public function __construct(
        ShortpositionActivityLogService $shortpositionActivityLogService,
        PaymentService $paymentService
    )
     {
        $this->shortpositionActivityLogService = $shortpositionActivityLogService;
        $this->paymentService = $paymentService;
     }

    /**
     * Retrieve short position activity log
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShortpositionActivityLog() 
    {

        try {
            $userId = Auth::user()->id;
            $logsArr = array();
            $logs = $this->shortpositionActivityLogService
                ->getShortpositionActivityLog($userId);
            if ($logs) {

                foreach ($logs as $log) {
                    $action = $log['action'];
                    $log = json_decode($log['log']);
                    $added = isset($log->companies->added) ? $log->companies->added:[];
                    $removed = isset($log->companies->removed) ? $log->companies->removed:[];
                    if (count($added)) {
                        $companies = [];
                    } else {
                        $companies = isset($log->companies)?$log->companies:[];
                    }

                    array_push(
                        $logsArr, 
                        array(
                        'action' => $action,
                        'companies' => $companies,
                        'added' => $added,
                        'removed' => $removed,
                        'subscription_id' => $log->subscription_id,
                        'created_at' => $log->created_at,
                        'price' => !empty($log->price)? number_format($log->price,2)  : null
                        )
                    );
                }

                return response()->json(['data' => $logsArr], 200);
            }

        } catch (Exception $e) {
            return response()->json(
                [$this->error => __('messages.un_processable_request')
                ],
                400
            );
        }


    }

    public function getLatestDonwGradeActivityLog() 
    {
        try {
            $userId = Auth::user()->id;
            $logsArr = array();
            $downGrade =  Config::get ( 'custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS' )[ 'Downgrade' ];
            $downGradeTrigger =  Config::get ( 'custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS' )[ 'Downgrade_trigger' ];
            $objDbDetaials = Helper::getModuleSucriptionPlanId($userId);

            $currentplanInfo = $this->paymentService->getSubcriptionPlanInfo(
                $objDbDetaials->subscription_id
            );

            if($currentplanInfo['plan_id'] !== $objDbDetaials->plan_id ){

                $objShortPositionDbDetaials =  Helper::getLatestSubcriptionActivity($userId);
                $downGrade = $objShortPositionDbDetaials->action;

            }

            $log = $this->shortpositionActivityLogService
                ->getLatestDownGradeActivityInfo(
                    $userId,
                    $downGrade,
                    $downGradeTrigger
                );

            if (!empty($log)) {
                $action = $log['action'];
                $log = json_decode($log['log']);
                $planInfo = $this->paymentService->getSubcriptionPlanInfo(
                    $log->subscription_id
                );
                $subcriptionStartDate = '';
                $subcriptionEndDate = '';

                if(!empty($log->subcription_start_date) 
                    && $log->subcription_end_date
                ){
                    $subcriptionStartDate = ($log->subcription_start_date);
                    $subcriptionStartDate = substr($log->subcription_start_date, 0, 10);
                    $subcriptionEndDate = ($log->subcription_end_date);
                    $subcriptionEndDate = substr($subcriptionEndDate, 0, 10);

                }

                $logsArr =    array(
                    'action' => $action,
                    'companies' => $log->companies,
                    'subscription_id' => $log->subscription_id,
                    'subcription_start' => !empty($subcriptionStartDate)?
                        $subcriptionStartDate : '',
                    'subcription_end_date' => !empty($subcriptionEndDate) ?
                        $subcriptionEndDate:'',
                    'subscription_level'=>!empty($planInfo['plan_name'])?
                        $planInfo['plan_name'] : '',
                    'created_at' => $log->created_at, 'price' =>
                        !empty($log->price) ?
                        $log->price: '');


                return response()->json(['data' => $logsArr], 200);
            }
            return response()->json(['data' => []], 200);
        } catch (Exception $e) {
            return response()->json(
                [
                $this->error => __('messages.un_processable_request')
                ],
                400
            );
        }

    }


}
