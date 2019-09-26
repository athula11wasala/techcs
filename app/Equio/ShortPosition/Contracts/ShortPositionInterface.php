<?php

namespace App\Equio\ShortPosition\Contracts;

/**
 * Interface ShortPositionInterface
 * 
 * @package App\Equio\ShortPosition
 */
interface ShortPositionInterface
{

    /**
     * Get subscription 
     * 
     * @param String $email 
     */
    public function getSubscriptionByEmail($email = '');

    /**
     * Get customer 
     * 
     * @param String $email 
     */
    public function getCustomerByEmail($email = '');

    public function getCustomerCardId($email = '');

    public function getCustomerId($email = '');

    public function createCustomer($email = '', $token = '');

    public function generateModuleSubscriptionTrackerArray($data = []);

    public function generateUserShortTrackerArray($data = []);

    public function generateShortPositionActivityLogArray($data = []);

    public function getUpcomingInvoice($email = '');

    public function updateSubscription($data = []);

    public function updateSubscriptionItemQuantity(
        $subscriptionId = '', $quantity = null
    );

    public function updateSubscriptionMetaData($data = []);

    public function getSubscriptionById($subscriptionId = '');

    public function getProrationAmount($email = '');

    public function getPlan($planMeta = '');
}
