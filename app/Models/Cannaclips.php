<?php

namespace App\Models;

use App\Equio\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use StudioNet\ScoreSearch\Searchable;

class Cannaclips extends Model
{

    use Searchable;

    protected $table = "cannaclips";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'name', 'description', 'link'
    ];

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    protected $searchable;

    protected $hidden = [

    ];

    public function __construct()
    {
        $this->searchable = Config::get('searchscore.CHANNACLIP');
    }

    public function getThumbnailAttribute($value)
    {
        // return  Helper::getYouTubeThumbnil ( $value );
        return Helper::UrlExtesnionTCallThmbnil($value);
    }


    public function getvideoAttribute($value)
    {
        if (!empty($value)) {
            return Helper::getYouTubeURL($value);
        }
        return '';

    }

    public function getDescriptionAttribute($value)
    {

        if (!empty($value)) {
            $length = strlen($value);

            if ($length >= 500) {

                return substr($value, 0, 500) . "...";
            }
            return $value;
        }
        return '';
    }

    public function getLinkAttribute($value)
    {
        // return  Helper::getYouTubeThumbnil ( $value );
        return Helper::getYouTubeURL($value);
    }

}


