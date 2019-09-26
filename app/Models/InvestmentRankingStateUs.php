<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentRankingStateUs extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = "mysql_external_intake";
    protected $table = "investment_ranking_state_us";
    protected $primaryKey = 'id';

    public static $table_connection = "external_intake.investment_ranking_state_us";


    protected $fillable = [
        'id','state','legalization','cultivation','retail','manufacturing','distribution','ancillary','risk',
        'opportunity','description','dataset_id','latest'
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
