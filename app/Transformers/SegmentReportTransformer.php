<?php

namespace App\Transformers;

use Illuminate\Support\Facades\Config;

/**
 * Class SegmentReportTransformer
 * @package App\Transformers
 */
class SegmentReportTransformer extends Transformer
{
    /**
     * Transform an item
     *
     * @param $item
     * @param array $option
     * @return mixed
     */
    public function transform($item, array $option = [])
    {
        return [
            'id' => (INT)$item['id'],
            'name' => $item['name'],
            'cover' => $this->urlEncoderWithBase($item['cover'], Config::get('custom_config.REPORTS_COVER')),
        ];
    }

    private function urlEncoderWithBase($url, $folder)
    {
        $fullUrl = Url('/') . Config::get('custom_config.REPORTS_STORAGE') . $folder . $url;
        return urldecode($fullUrl);
    }
}
