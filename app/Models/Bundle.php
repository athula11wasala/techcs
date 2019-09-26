<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "bundle";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id','name','description','purchase_url_link','cover_image','price','offer','status'
    ];
    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function getCoverImageAttribute($value)
    {
        return url('/') . "/storage/reports/cover/" . $value;
    }

}
