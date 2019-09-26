<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;


class SaleProjectionsRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\SaleProjections';
    }

    public function allSaleProjectionDetails(){

        $result = $this->model
            ->where ( "latest", 1)
            ->get ();

        foreach ($result as $saleProjection) {
                        $data[$saleProjection->year]['medical'] = $saleProjection->medical;
                        $data[$saleProjection->year]['recreational'] = $saleProjection->recreational;
                        $data[$saleProjection->year]['total'] = $saleProjection->total;
        }

        return $data;

    }


}