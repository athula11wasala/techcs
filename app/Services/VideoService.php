<?php
/**
 * Created by PhpStorm.
 * User: thilan
 * Date: 7/3/18
 * Time: 4:02 PM
 */

namespace App\Services;


class VideoService
{

    public function __construct()
    {

       /* $this->woocommerce = new Client(
            Config::get('custom_config.WOOCOMMERCE_API_URL'),
            Config::get('custom_config.WOOCOMMERCE_API_KEY'),
            Config::get('custom_config.WOOCOMMERCE_API_SECRET'),
            ['wp_api' => true, 'version' => 'wc/v1',]
        );*/
    }

    public function getYoutubeThumbnil(){
        /*https://img.youtube.com/vi/<insert-youtube-video-id-here>/default.jpg*/
    }


    public function getTedThumbnil(){
        /*$source = 'http://www.ted.com/talks/andy_puddicombe_all_it_takes_is_10_mindful_minutes';
        $tedJson = json_decode(file_get_contents('http://www.ted.com/talks/oembed.json?url='.urlencode($source)), TRUE);
        \Log::info($tedJson);
        \Log::info(" *************** TED VIDEO *******************");*/
    }

    public function getvimeoThumbnil(){

        /*$tedJson = json_decode(file_get_contents('https://api.vimeo.com/videos/277826934/pictures', TRUE);

        \Log::info();
        \Log::info(" ++++++++++++++++++ VIMEO VIDEO +++++++++++++++++ ");*/
    }




}