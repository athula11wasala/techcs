<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;


class PosSettingsRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\PosSettings';
    }

    /**
     * Returns list of best/worst product list and prices for each locations/licences with national average
     * @param $licenses
     * @return mixed
     */
    public function getSettings($email, $order="DESC")
    {
        $result = DB::connection('mysql_external_intake')->select( DB::raw("
                SELECT *
                FROM pos_settings
                WHERE pos_settings.email = ('$email');
        ") );
        \Log::info("==== PosSettingsRepository->getSettings ", ['u' => json_encode($result)]);
        return $result;
    }

    /**
     * Returns list of best/worst product list and prices for each locations/licences with national average
     * @param $licenses
     * @return mixed
     */
    public function updateSettings($email, $quickbooks_clientid, $quickbooks_secret, $tweeter_name,
                                   $facebook_userid, $instagram_userid, $instagram_clientid, $instagram_secret,
                                   $pos_name, $pos_apikey, $pos_username, $pos_password)
    {
        $result = DB::connection('mysql_external_intake')->select(DB::raw("
              UPDATE `pos_settings` SET
                  `QuickbooksClientid`='$quickbooks_clientid',
                  `QuickbooksSecret`='$quickbooks_secret',
                  `TweeterName`='$tweeter_name',
                  `FacebookUserid`='$facebook_userid',
                  `InstagramUserid`='$instagram_userid',
                  `InstagramClientid`='$instagram_clientid',
                  `InstagramSecret`='$instagram_secret',
                  `PosName`='$pos_name',
                  `PosApikey`='$pos_apikey',
                  `PosUsername`='$pos_username',
                  `PosPassword`='$pos_password'
              WHERE email='$email';
        "));
        \Log::info("==== PosSettingsRepository->updateSettings ", ['u' => json_encode($result)]);
        return $result;
    }

}