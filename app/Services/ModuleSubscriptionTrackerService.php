<?php

namespace App\Services;

use App\Repositories\ModuleSubscriptionTrackerRepository;

class ModuleSubscriptionTrackerService {

    private $moduleSubscriptionTrackerRepository;

    /**
     * ModuleSubscriptionTrackerService constructor.
     * @param ModuleSubscriptionTrackerRepository $moduleSubscriptionTrackerRepository
     */
    public function __construct(
        ModuleSubscriptionTrackerRepository $moduleSubscriptionTrackerRepository
    ) 
    {
        $this->moduleSubscriptionTrackerRepository = $moduleSubscriptionTrackerRepository;
    }

    public function storeSubscriptionTrackerDetails($subscriptionData = array()) 
    {
        return $this->moduleSubscriptionTrackerRepository
            ->storeSubscriptionTrackerDetails($subscriptionData);
    }

    public function shortPositionSubscriptionStatus($userId = 0) 
    {
        return $this->moduleSubscriptionTrackerRepository
            ->shortPositionSubscriptionStatus($userId);
    }

    public function subscriptionAvailability($companyCode = '', $userId = 0) 
    {
        return $this->moduleSubscriptionTrackerRepository
            ->subscriptionAvailability($companyCode, $userId);
    }

    public function updateSubscriptionStatus(
        $data = [], 
        $subscriptionId = ''
    ) {
    
        return $this->moduleSubscriptionTrackerRepository
            ->updateSubscriptionStatus($data, $subscriptionId);
    }

    public function updateSubscriptionPlanId(
        $data = [],
        $subscriptionId = ''
    ) {
    
        return $this->moduleSubscriptionTrackerRepository
            ->updateSubscriptionPlan($data, $subscriptionId);
    }

    public function getPlan($subscriptionId = null)
    {
        return $this->moduleSubscriptionTrackerRepository
            ->getPlan($subscriptionId);
    }

}