<?php

namespace App\Services;

use App\Repositories\Criteria\Users\OrderByCreated;
use App\Repositories\InstagramMediaDailyViewRepository;
use Join;

class InstagramService
{
    private $instagramMediaDailyViewRepository;

    /**
     * PosService constructor.
     * @param $posRepository
     */
    public function __construct(InstagramMediaDailyViewRepository $instagramMediaDailyViewRepository)
    {
        $this->instagramMediaDailyViewRepository = $instagramMediaDailyViewRepository;
    }

    /**
     * @return mixed
     */
    public function getLikesCount()
    {
        return $this->instagramMediaDailyViewRepository->getLikesCount();
    }

}


