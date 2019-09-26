<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartKeywords extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "charts_keywords";

    protected $fillable = [
        'charts_id', 'keywords_id'
    ];

    public $timestamps = false;



}
