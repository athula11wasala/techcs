<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use StudioNet\ScoreSearch\Searchable;
use App\Equio\Helper;

class Webinar extends Model
{
    use Searchable;

    protected $table = "webinars";

    protected $primaryKey = 'id';

    protected $fillable = [
        'period', 'period', 'description_short', 'description_long', 'duration', 'link', 'full_pdf'
    ];

    public $timestamps = true;

    Protected $subscriptionLevel;

    Protected $allsubscriptionName = ["premium", "premium_plus", "enterprise"];

    protected $searchable;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function __construct()
    {
        $this->searchable = Config::get('searchscore.WEBINARS');
    }


    public function getFullPdfAttribute($value)
    {


        $this->subscriptionLevel = Auth::user()->subscription_level;

        if ($this->subscriptionLevel == "essential") {

            return url('/') . Config::get('custom_config.WEBINAR_FULL_PDF') . $value;

        } else {
            if (in_array($this->subscriptionLevel, $this->allsubscriptionName)) {

                return '';
            }
        }
    }


    public function getThumbnailAttribute($value)
    {

        $this->subscriptionLevel = Auth::user()->subscription_level;

        if (Helper::checkAdminstratorRole(Auth::user()->id) == true) {

            return Helper::getYouTubeThumbnil($value);
        }


        if ($this->subscriptionLevel == "essential") {
            return Helper::getYouTubeThumbnil($value);

        } else {

            if (in_array($this->subscriptionLevel, $this->allsubscriptionName)) {

                return Helper::getYouTubeThumbnil($value);

            }
        }

    }

    public function getLinkAttribute($value)
    {
        return Helper::getYouTubeURL($value);
    }

    public function getDurationAttribute($value)
    {
        $this->subscriptionLevel = Auth::user()->subscription_level;

        if (Helper::checkAdminstratorRole(Auth::user()->id) == true) {
            return $value;
        }

        if ($this->subscriptionLevel == "essential") {
            return '';

        } else {

            if (in_array($this->subscriptionLevel, $this->allsubscriptionName)) {

                return $value;

            }
        }


    }


    public function getCoverAttribute($value)
    {
        $this->subscriptionLevel = Auth::user()->subscription_level;

        if (Helper::checkAdminstratorRole(Auth::user()->id) == true) {

            return url('/') . "/storage/webinars/cover/" . $value;
        }


        if ($this->subscriptionLevel == "essential") {
            return url('/') . "/storage/webinars/cover/" . $value;

        } else {

            if (in_array($this->subscriptionLevel, $this->allsubscriptionName)) {

                return url('/') . "/storage/webinars/cover/" . $value;

            }
        }

    }



    public function getvideoAttribute($value)
    {
        return Helper::getYouTubeURL($value);

    }

    /*  public function getvideoAttribute($value)
      {

          $this->subscriptionLevel = Auth::user()->subscription_level;

          if (Helper::checkAdminstratorRole(Auth::user()->id) == true) {

              return Helper::getYouTubeURL($value);
          }


          if ($this->subscriptionLeve == "essential") {
              return Helper::getYouTubeURL($value);

          } else {

              if (in_array($this->subscriptionLevel, $this->allsubscriptionName)) {

                  return Helper::getYouTubeURL($value);

              }
          }

      }
  */




}



