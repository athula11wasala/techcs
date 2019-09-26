<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "interest";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'name', 'type',  'created_at', 'updated_at'
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
