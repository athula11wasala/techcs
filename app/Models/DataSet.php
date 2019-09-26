<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class DataSet extends Model
{
    protected $table = "data_sets";

    protected $primaryKey = 'id';
    protected $id = '';


    /* public function cannabisBenchmarksUs()
       {
           return $this->belongsTo('App\Models\CannabisBenchmarksUs','dataset_id','data_set');
       }


    public function cannibalization()
    {
        return $this->belongsTo('App\Models\Cannibalization','dataset_id','data_set');
    }


    public function taxRate()
    {
        return $this->belongsTo('App\Models\TaxRates','dataset_id','data_set');
    }

     public function qualifyingCondition()
     {
         return $this->hasMany('App\Models\QualifyingCondition','data_set','dataset_id');
     }


    /* public function stateLegalized()
     {
         return $this->hasMany('App\Models\StateLegalized','data_set','dataset_id');
     }
    */


    /* public function cannabisBenchmarksUs()
     {
         return $this->belongsTo('App\Models\CannabisBenchmarksUs','dataset_id','data_set');
     }
    */

    public function getIdAttribute($value)
    {
        $this->id   = $value;
        return $value;
    }

    public function getStatusAttribute($value)
    {
        if(!empty($this->id)){

            if($this->id == $value){
                return 'Active';
            }

        }
        return 'Inactive';

    }

    public function getfromAttribute($value)
    {

        if(!empty($value)){

          return  date("m-d-Y h:m:s", strtotime($value));
        }
       return  '';

    }

    public function getToAttribute($value)
    {
        return $value;
        if(!empty($value)){

            return  date("m-d-Y h:m:s", strtotime($value));
        }
       return  '';

    }

    public function getCreatedAtAttribute($value)
    {
        return $value;
        if (!empty($value)) {

            return date("m-d-Y h:m:s", strtotime($value));
        }
        return '';
    }

}
