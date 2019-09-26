<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class UserShortTracker extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'user_short_trackers';



    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'symbol',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

        'id', 'created_at', 'updated_at'
    ];

    /**
     * Get the user that owns the short tracker company list.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }


}
