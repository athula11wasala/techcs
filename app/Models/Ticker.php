<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticker extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $connection = "mysql_external_intake";

    protected $table = "ticker";

    protected $primaryKey = 'id';

    protected $fillable = [
        'symbol',
        'company',
        'currency',
        'sector',
        'last',
        'open',
        'volume',
        'published_date',
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