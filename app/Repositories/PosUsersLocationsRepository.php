<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;


class PosUsersLocationsRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\PosUsersLocations';
    }

    /**
     * Returns list of POS licenses by email (connected user)
     * @param $email
     * @return mixed
     */
    public function getLicenses($email)
    {
        \Log::info("==== getLicenses ", ['u' => json_encode($email)]);
        $licenses = $this->model->where('email', '=', $email)
            ->select(['id', 'email', 'LicenseNumber'])->get();
        \Log::info("==== getLicenses ", ['u' => json_encode($licenses)]);
        return $licenses;
    }

}