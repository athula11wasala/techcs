<?php

namespace App\Repositories;

use App\Models\Interst;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;


class InterestRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\Interest';
    }

    public function showInterestInfo()
    {
        $result = $this->model
            ->select(
                [
                    'id', 'name', 'type'
                ])
            ->orderBy("name", "asc")
            ->get();
        $data = [];
        $i = null;

        foreach ($result as $rows) {
            if ($rows->type == 1) {
                $data['type'][$rows->type][] = [
                    'id' => $rows->id,
                    'name' => $rows->name,
                ];

            } elseif ($rows->type == 2) {

                $data['type'][$rows->type][] = [
                    'id' => $rows->id,
                    'name' => $rows->name
                ];
            }
        }
        return $data;

    }


}