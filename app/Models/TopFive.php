<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use StudioNet\ScoreSearch\Searchable;
use Illuminate\Support\Facades\Config;

class TopFive extends Model
{

    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = "";

    protected $table = 'insight_daily_us';


    protected $fillable = [
        'date', 'source', 'headline', 'full_story', 'image_url', 'topic', 'source_url'
    ];

    protected $searchable ;

    public function __construct()
    {
        $this->searchable =  Config::get ( 'searchscore.TOP5' );
    }

    public $timestamps = false;


    public function getCategoryImageAttribute($value)
    {
        switch ($value) {
            case "Financial":
                return url ( '/' ) . "/storage/top5/category/CannabisInSight-Icons-Finance.png";
                break;
            case "Legal":
                return url ( '/' ) . "/storage/top5/category/CannabisInSight-Icons-Legislative.png";
                break;
            case "Wildcard":
                return url ( '/' ) . "/storage/top5/category/CannabisInSight-Icons-WildCard.png";
                break;
            case "Medical":
                return url ( '/' ) . "/storage/top5/category/CannabisInSight-Icons-Medical.png";
                break;
            case "Social":
                return url ( '/' ) . "/storage/top5/category/CannabisInSight-Icons-Social&Cultural.png";
                break;
            case "Tech, Science & Innovation":
                return url ( '/' ) . "/storage/top5/category/tech-icon.png";
                break;
            case "International":
                return url ( '/' ) . "/storage/top5/category/Intl-icon.png";
                break;
            case "Hemp":
           return url ( '/' ) . "/storage/top5/category/CannabisInSight-Icons-Hemp.png";
                break;
            case "InFocus":
                return url ( '/' ) . "/storage/top5/category/CannabisInSight-Icons-InFocus.jpg";
                break;
            default:
                return null;
        }

    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }


}



