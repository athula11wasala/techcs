<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleReport extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "bundle_report";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id','report_id','bundle_id'
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
