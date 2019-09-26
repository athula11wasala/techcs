<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CannabisBenchmarksUs extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = "mysql_cms";

    protected $table = "cannabis_benchmarks_us";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'week_ending', 'spot', 'indoor', 'greenhouse', 'outdoor', 'medical', 'adult_use', 'all_data'
    ];
    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    protected $hidden = [];


    /* public function dataSet()
       {
           return $this->hasMany('App\Models\DataSet','data_set','dataset_id');
       }
      */


    /* public function dataSet()
     {
         return $this->hasMany('App\Models\DataSet','data_set','dataset_id');
     }
    */

}
