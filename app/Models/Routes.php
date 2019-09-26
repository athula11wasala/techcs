<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Routes extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "route";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id','routes','created_at','updated_at'
    ];
    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];


    public function subscritptions()
    {
        return $this->belongsToMany( 'App\Models\Subscription', 'subscription_route', 'subscription_id','route_id' );

    }



}
