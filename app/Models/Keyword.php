<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "keywords";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'name', 'created_at', 'updated_at'
    ];

    public $timestamps = false;
}
