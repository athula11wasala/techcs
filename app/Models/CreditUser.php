<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditUser extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "credit__user";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id','name','description','description','type'
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
