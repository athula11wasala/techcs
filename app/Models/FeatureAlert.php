<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class FeatureAlert extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "new_features";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'title', 'description', 'image', 'link', 'active', 'created_at', 'updated_at'
    ];

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function getImageAttribute($value)
    {
        return !empty($value) ? url('/') . "/" . Config::get('custom_config.alert_image') . $value : null;
    }

    public function getCreatedAtAttribute($value)
    {
        return !empty($value) ? date('m-d-Y h:i:s', strtotime($value)) : '';
    }

    public function getUpdatedAtAttribute($value)
    {
        return !empty($value) ? date('m-d-Y h:i:s', strtotime($value)) : '';
    }
}





