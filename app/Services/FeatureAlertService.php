<?php

namespace App\Services;

use App\Repositories\Criteria\Users\OrderByCreated;
use App\Repositories\FeatureAlertRepository;
use Join;

class FeatureAlertService
{

    private $featureAlertRepository;

    /**
     * FeatureAlertService constructor.
     * @param FeatureAlertRepository $featureAlertRepository
     */
    public function __construct(FeatureAlertRepository $featureAlertRepository)
    {
        $this->featureAlertRepository = $featureAlertRepository;
    }

    public function getAll($request)
    {
        return $this->featureAlertRepository->getAllAlert ( $request );
    }

    public function createAlert($alertArray)
    {
        return $this->featureAlertRepository->saveAlert ( $alertArray );
    }

    public function getAllNotification($user)
    {
        return $this->featureAlertRepository->getAllNotification ( $user );
    }


    public function getFeatureById($id)
    {
        return $this->featureAlertRepository->featureInfoById ( $id );
    }

    public function getUpdateFeature($alertArray)
    {
        return $this->featureAlertRepository->updateFeature ( $alertArray );
    }

    public function getUpdateStatus($alertArray)
    {
        return $this->featureAlertRepository->updateFeatureStatus ( $alertArray );
    }

    public function getCheckFeatureTitle($request)
    {
        $title = !empty( $request[ 'feature_title' ] ) ? $request[ 'feature_title' ] : 0;
        $id = !empty( $request[ 'id' ] ) ? $request[ 'id' ] : 0;
        return $this->featureAlertRepository->checkExistFeatureTitle ( $title, $id );
    }

}


