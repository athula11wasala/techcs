<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualifyingCondition extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = "mysql_external_intake";

    protected $table = "qualifying_conditions";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'state','alzheimer','lou_gehrig','anorexia','debilitating',
        'arnold_chiari','arthritis','cachexia_wasting','cancer','causalgia','sickle_anemia'
    ];

    public static $table_connection = "external_intake.qualifying_conditions";

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
