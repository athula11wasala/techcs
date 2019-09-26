<?php

namespace App\Transformers;

/**
 * Class Transformer
 * @package App\Transformers
 */
abstract class Transformer
{

    /**
     * Transform a collection of items
     *
     * @param $items
     * @return array
     */
    public function transformCollection($items)
    {
        return array_map([$this, 'transform'], $items->toArray());
    }

    /**
     * Transform array
     * @param array $items
     * @return array
     */
    public function transformArray(array $items)
    {
        return array_map([$this, 'transform'], $items);
    }

    /**
     * Transform a collection with extra arguments
     * @param array $items
     * @param array $option
     * @return array
     */
    public function transformCollectionWithExtraArguments(array $items, array $option)
    {
        return array_map(function ($item) use ($option) {
            return $this->transform($item, $option);
        }, $items);
    }

    /**
     * Transform an item
     * @param $item
     * @param array $options
     * @return mixed
     */
    public abstract function transform($item, array $options = []);
}