<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;

class ShortpositionActivityLogRepository extends Repository {

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model() {
        return 'App\Models\ShortpositionActivityLog';
    }

    /**
     * save ShortpositionActivityLog data
     * @return array
     */
    public function storeShortpositionActivityLog($data = array()) {

        $results = $this->saveModel($data);

        return $results;
    }

    /**
     * Retrieve log
     */
    public function getShortpositionActivityLog($userId = '') {

        $shortPositionActivityLogs = $this->model->where('shortposition_activity_log.user_id', '=', $userId)
            ->select('*')
            ->get();

        $shortPositionActivityLogs = collect($shortPositionActivityLogs);
        return $shortPositionActivityLogs;

    }

    public function getLatestUserAction($userId) {

        $shortPositionActivityLogs = $this->model->where('shortposition_activity_log.user_id', '=', $userId)
            ->select('action')
            ->orderBy("id", "desc")
            ->first();

        if (!empty($shortPositionActivityLogs->action)) {

            return $shortPositionActivityLogs->action;
        }
        return false;

    }


    /**
     * get latst donwgrade activity log details
     * @param string $userId
     * @param string $action
     * @return bool|\Illuminate\Support\Collection
     */
    public function getLatestDownGradeActivityLog($userId = '', $action = "",$downGradeTrigger='') {

        if(!empty($downGradeTrigger)){

            if (!empty($this->getLatestUserAction($userId)) && $this->getLatestUserAction($userId) == $downGradeTrigger) {

                $shortPositionStatusCompete = $this->model->where('shortposition_activity_log.user_id', '=', $userId)
                    ->Where("action", $action)
                    ->select('id')
                    ->orderBy("id", "desc")
                    ->first();

                $shortPositionActivityLogs = $this->model->where('shortposition_activity_log.user_id', '=', $userId)
                    ->Where("action", $downGradeTrigger)
                    ->select('log', 'action','id')
                    ->orderBy("id", "desc")
                    ->first();

                 if(empty($shortPositionStatusCompete) ||  $shortPositionActivityLogs->id >  $shortPositionStatusCompete->id )
                 {
                     return collect($shortPositionActivityLogs);
                 }
            }

        }

        if (!empty($this->getLatestUserAction($userId)) && $this->getLatestUserAction($userId) == $action) {

            if($this->getLatestUserAction($userId) ==
                Config::get ( 'custom_config.SHORT_POSITION_ACTIVITY_LOG_STATUS' )[ 'Downgrade' ] ){

                return false;
            }

            $shortPositionActivityLogs = $this->model->where('shortposition_activity_log.user_id', '=', $userId)
                ->Where("action", $action)
                ->select('log', 'action','id')
                ->orderBy("id", "desc")
                ->first();

            return collect($shortPositionActivityLogs);
        }
        return false;

    }

}