<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;


class PosLocationsRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\PosLocations';
    }

    /**
     * Returns location information of a license number
     * @param $LicenseNumber
     * @return mixed
     */
    public function getLocationDetail($LicenseNumber)
    {
        \Log::info("==== getLocationDetail->LicenseNumber ", ['u' => json_encode($LicenseNumber)]);
        $locationDetail =$this->model->where('LicenseNumber', '=', $LicenseNumber)
            ->select(['id', 'Zip', 'City', 'LicenseNumber'])
            ->first();
        \Log::info("==== getLocationDetail->locationDetail ", ['u' => json_encode($locationDetail)]);
        return $locationDetail;
    }

}