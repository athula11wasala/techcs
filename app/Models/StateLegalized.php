<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StateLegalized extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = "mysql_external_intake";
    protected $table = "state_legalized";
    protected $primaryKey = 'id';

    public static $table_connection = "external_intake.state_legalized";


    protected $fillable = [
        'id','map_order','state','use_estimate'
    ];
    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /* public function dataSet()
         {
             return $this->hasMany('App\Models\DataSet','data_set','dataset_id');
         }
        */



}
