<?php

namespace App\Services;


use App\Repositories\ZefyrRetailPriceDetailsRepository;
use App\Repositories\ZefyrConsumerGroupDetailsRepository;
use App\Repositories\Criteria\Users\OrderByCreated;

use Join;

class ConsumerIndexService
{


    private $zefyrRetailPriceDetailRepository;
    private $zefyrConsumerGroupDetailsRepository;
    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     */

    public function __construct(ZefyrRetailPriceDetailsRepository $zefyrRetailPriceDetailRepository,
                                ZefyrConsumerGroupDetailsRepository  $zefyrConsumerGroupDetailsRepository ) {
        $this->zefyrRetailPriceDetailRepository = $zefyrRetailPriceDetailRepository;
        $this->zefyrConsumerGroupDetailsRepository = $zefyrConsumerGroupDetailsRepository;
    }

    public function getZeferRetailPriceDetails($request) {
        return $this->zefyrRetailPriceDetailRepository->allRetailPriceDetailInfo($request);
    }

    public function getStateProductInfo($request) {
        return $this->zefyrRetailPriceDetailRepository->getStateProductInfo($request);
    }

    public function getConsumerGroupDetails($request) {
        \Log::info("==== ConsumerIndexService->getConsumerGroupDetails consumer-group before");
        $return = $this->zefyrConsumerGroupDetailsRepository->allConsumerGroupDetailInfo($request);
         \Log::info("==== ConsumerIndexService->getConsumerGroupDetails consumer-group after");
       return $return;
    }


}


