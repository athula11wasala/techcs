<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentRankingThresholdUs extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = "mysql_external_intake";
    protected $table = "investment_ranking_threshold_us";
    protected $primaryKey = 'id';

    public static $table_connection = "external_intake.investment_ranking_threshold_us";


    protected $fillable = [
        'id','segment','low_medium','medium_high','dataset_id','latest'
    ];
    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    


}
