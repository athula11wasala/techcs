<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRates extends Model
{
    protected $connection = "mysql_external_intake";


    protected $table = 'taxrates';


    /* public function dataSet()
     {
         return $this->hasMany('App\Models\DataSet','data_set','dataset_id');
     }
    */

}
