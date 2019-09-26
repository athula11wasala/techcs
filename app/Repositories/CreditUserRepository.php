<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\Profiles;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;


class CreditUserRepository extends Repository
{


    protected $perPage;
    protected $sort;
    protected $sortColumn;
    protected $is_backend;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\CreditUser';
    }


    public function allCreditUserInfo()
    {
        $object = $this->model->select ( "id", "name", "type" )->get ();


        $data[ 'author' ] = [];
        $data[ 'research' ] = [];
        $data[ 'editor' ] = [];
        $data[ 'markeing&PR' ] = [];
        foreach ( $object as $value ) {

            if ( $value->type == 1 ) {
                $data[ 'author' ] [] = ['id' => $value->id, 'name' => $value->name,];
            }
            if ( $value->type == 2 ) {
                $data[ 'research' ] [] = ['id' => $value->id, 'name' => $value->name,];
            }
            if ( $value->type == 3 ) {
                $data[ 'editor' ] [] = ['id' => $value->id, 'name' => $value->name,];
            }
            if ( $value->type == 4 ) {
                $data[ 'markeing&PR' ][] = ['id' => $value->id, 'name' => $value->name,];
            }


        }
        return $data;

    }

}
