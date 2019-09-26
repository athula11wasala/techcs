<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortpositionActivityLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "shortposition_activity_log";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id','user_id','action','log'
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
