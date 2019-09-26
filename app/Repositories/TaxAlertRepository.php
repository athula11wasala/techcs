<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;

class TaxAlertRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\TaxAlert';
    }

    /**
     * Returns tax likes data
     * @param $perPage
     * @param $orderByColumn
     * @param bool $desc
     * @param array $where
     * @return array
     */
    public function getTaxAlerts($perPage, $orderByColumn, $desc = false, $where = array())
    {
        $orderBy = ($desc) ? 'desc' : 'asc';

        $q = $this->model;

        if (!is_array($where) && !count($where)) {
            return [];
        }
        foreach ($where as $field => $value) {
            $q = $q->where($field, '=', $value);
        }

        return $q->orderBy($orderByColumn, $orderBy)->paginate($perPage);
    }

}
