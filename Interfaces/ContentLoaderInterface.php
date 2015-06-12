<?php

namespace Siso\Bundle\ContentLoaderBundle\Interfaces;

/**
 * Interface for content loader
 */
interface ContentLoaderInterface
{
    /**
     * Load content features from array
     *
     * @param array $data
     * @param array $parameters
     */
    public function load($data, $parameters = []);
}