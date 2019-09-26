<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;


class InstagramMediaDailyViewRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\InstagramMediaDailyView';
    }

    /**
     * Returns list of best/worst product list and prices for each locations/licences with national average
     * @param $licenses
     * @return mixed
     */
    public function getLikesCount()
    {
        $result = DB::connection('mysql_instagram_db')->select(DB::raw("
                SELECT @rownum := @rownum + 1 as row_number, Insta.hashtag, Insta.likes FROM (
                    SELECT hashtag, SUM(likes_count) as likes , hashtag as id
                    FROM `insta_media_daily_view`, `insta_original_tags` 
                    WHERE insta_media_daily_view.hashtag_id=insta_original_tags.hashtag_id
                    GROUP BY hashtag
                    ORDER BY likes DESC
                    LIMIT 20
                ) as Insta
                cross join (select @rownum := 0) r
                WHERE Insta.likes > 0
                ORDER BY likes DESC

        "));
        return $result;
    }
}