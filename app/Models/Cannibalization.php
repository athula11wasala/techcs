<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cannibalization extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = "mysql_external_intake";
    protected $table = "cannibalization";

    protected $primaryKey = 'id';

    public static $table_connection = "external_intake.cannibalization";

    protected $fillable = [
        'id','state_ABV','state','state_short','date','cannibalized_pharma','cannibalized_cigarettes',
        'cannibalized_non_cigarettes','cannibalized_tobacco','cigarettes','alcohol','quarter','cannibalized_alcohol'
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

}
