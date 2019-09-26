<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Config;

class ZefyrConsumerGroup extends Authenticatable {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = "mysql_external_intake";
    protected $table = "zefyr_consumer_group";
    protected $primaryKey = 'id';
    protected $type = '';

    protected $fillable = [
        'id', 'date', 'state', 'consumer_group,male_pop', 'female_pop', 'adult_dispensary_consumer',
        'adult_dispensary_total', 'medical_dispensary_consumer', 'medical_dispensary_total',
        'hybrid_dispensary_consumer', 'hybrid_dispensary_total', 'created_at', 'updated_at'
    ];

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function getConsumerImageAttribute($value) {
        if ( isset( $value ) ) {
            $data = url ( '/' ) .  Config::get ( 'custom_config.CONSUMER_ICON_STORAGE' ) . $value . '.png';
            return $data;
        }
        return '';
    }
}