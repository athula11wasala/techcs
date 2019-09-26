<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\HasApiTokens;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use App\Models\Report;

class ZefyrRetailPrice extends Authenticatable
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = "mysql_external_intake";
    protected $table = "zefyr_retail_price";

    protected $primaryKey = 'id';
    protected $type = '';

    protected $fillable = [
        'id', 'market', 'state', 'product_category', 'sub_type', 'quntity_type', 'avg_price','min_price','max_price', 'date','created_at', 'updated_at'
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