<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptonRoute extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "subscription_route";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'route_id', 'subscription_id', 'created_at', 'updated_at'
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
