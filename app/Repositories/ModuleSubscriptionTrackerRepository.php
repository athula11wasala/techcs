<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;

class ModuleSubscriptionTrackerRepository extends Repository {

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model() {
        return 'App\Models\ModuleSubscriptionTracker';
    }

    /**
     * get all companies list from ticker table
     * @return array
     */
    public function storeSubscriptionTrackerDetails($subscriptionData = array()) {

        $results = $this->saveModel($subscriptionData);

        return $results;
    }

    /**
     * get short position subscription status
     * @return array
     */
    public function shortPositionSubscriptionStatus($userId = 0) {

        $shortPositionSubscription = $this->model->where ( 'module_subscription_trackers.user_id', '=', $userId )
            ->orderBy('created_at', 'DESC')
            ->select (
                [
                    'module_subscription_trackers.subscription_status'
                ]
            )
            ->first();

         $shortPositionSubscription = collect ( $shortPositionSubscription );
         return $shortPositionSubscription;
    }

    /**
     * get short position subscription status
     * @return array
     */
    public function subscriptionAvailability($companyCode = '', $userId = 0) {

        $active = Config::get(
            'custom_config.MODULE_SUBCRIPTION_TRACKERS_STATUS.active'
        );
        $pendingCancel = Config::get(
            'custom_config.MODULE_SUBCRIPTION_TRACKERS_STATUS.pending_cancel'
        );

        $shortPositionSubscription = DB::select(
            "select * from `module_subscription_trackers` 
            where `module_subscription_trackers`.`user_id` = $userId 
            and (`module_subscription_trackers`.`subscription_status` = $active 
            or `module_subscription_trackers`.`subscription_status` = $pendingCancel)
            "
        );

        $subscriptionForCompany = DB::select(
            "select * from `module_subscription_trackers` 
            inner join `user_short_trackers` 
            on `user_short_trackers`.`user_id` 
            = `module_subscription_trackers`.`user_id` 
            where `module_subscription_trackers`.`user_id` = $userId 
            and (`module_subscription_trackers`.`subscription_status` = $active 
            or `module_subscription_trackers`.`subscription_status` = $pendingCancel)
            and (`user_short_trackers`.`symbol` = '$companyCode' 
            or `user_short_trackers`.`symbol` = 'UNLIMITED')"
        );

        return array(
            'short_position_subscription'=>$shortPositionSubscription, 
            'subscription_for_company'=>$subscriptionForCompany
        );

        
    }

    /**
     * Update subscription status
     * @return array
     */
    public function updateSubscriptionStatus(
        $data = [], $subscriptionId = ''
    ) {
        $userId = null;
        $isUpdated = $this->update(
            $data, $subscriptionId, 'subscription_id'
        );

        if ($isUpdated) {
            $result = $this->model->where(
                'module_subscription_trackers.subscription_id', '=', $subscriptionId
            )
                ->select('user_id')
                ->first();
            $userId = $result['user_id'];
        }

        return $userId;
    }

    /**
     * Update subscription Plan
     * @return array
     */
    public function updateSubscriptionPlan(
        $data = [], $subscriptionId = ''
    ) {
        $userId = null;
        $isUpdated = $this->update(
            $data, $subscriptionId, 'subscription_id','status', Config::get ( 'custom_config.MODULE_SUBCRIPTION_TRACKERS_STATUS.active' )
        );

        if ($isUpdated) {
            $result = $this->model->where(
                'module_subscription_trackers.subscription_id', '=', $subscriptionId
            )
                ->select('user_id')
                ->first();
            $userId = $result['user_id'];
        }

        return $userId;
    }



    public function getPlan($subscriptionId = null)
    {
        $result = $this->model->where(
            'module_subscription_trackers.subscription_id', '=', $subscriptionId
        )
            ->select('plan_id')
            ->first();
        return $result['plan_id'];
    }


}