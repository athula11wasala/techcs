<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleSubscriptionTracker extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "module_subscription_trackers";

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'subscription_id',
        'plan_id',
        'payment_gateway',
        'subscription_status',
        'module_name',
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
    ];
}
