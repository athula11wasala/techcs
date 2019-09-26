<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "subscription";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id','display_name','code','status','created_at','updated_at'
    ];
    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function routes()
    {
        return $this->belongsToMany( 'App\Models\Routes', 'subscription_route', 'route_id','subscription_id' );

    }


}
