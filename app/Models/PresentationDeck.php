<?php

namespace App\Models;

use App\Equio\Helper;
use Illuminate\Database\Eloquent\Model;

class PresentationDeck extends Model
{
    protected $table = "presentation_decks";

    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'date', 'title', 'description', 'cover', 'link'
    ];

    public $timestamps = true;


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function getthumbnilAttribute($value)
    {

        return Helper::UrlExtesnionType($value);
    }

    public function getLinkAttribute($value)
    {

        if(  Helper::UrlExtesnionType($value) == "youtube"){
            return Helper::getYouTubeURL ( $value );
        }
        return $value;
    }

}



