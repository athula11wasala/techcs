<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAcknowledgement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "user_acknowledgements";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'user_id', 'feature_id', 'status'
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
