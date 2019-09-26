<?php

namespace App\Transformers;

/**
 * Class ApplicabilityTransformer
 * @package App\Transformers
 */
class CompanyProfileNamesTransformer extends Transformer
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

            'country' => $item['country'],

            'profile_cover' => url('/') . "/" . $item['profile_cover'],

            'profile_document' => url('/') . "/" . $item['profile_document'],

            'profile_type' => (INT)$item['profile_type'],

            'profile_order' => (INT)$item['profile_order'],


        ];
    }
}
