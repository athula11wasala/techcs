<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use StudioNet\ScoreSearch\Searchable;

class Blog extends Model
{
    use Searchable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "blogs";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'title', 'description', 'date', 'image_url', 'link', 'created_at', 'updated_at'
    ];

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $searchable;

    public function __construct()
    {
        $this->searchable = Config::get ( 'searchscore.BLOG' );
    }

    public function getTableColumns()
    {
        return $this->getConnection ()->getSchemaBuilder ()->getColumnListing ( $this->getTable () );
    }

    public function getDescriptionAttribute($value)
    {
        if ( !empty( $value ) ) {
            $length = strlen ( $value );

            if ( $length >= 500 ) {

                return substr ( $value, 0, 500 ) . "...";
            }
            return $value;
        }
        return '';

    }

}