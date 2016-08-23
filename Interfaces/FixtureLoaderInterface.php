<?php

namespace Siso\Bundle\ContentLoaderBundle\Interfaces;

/**
 * Interface for fixture loader
 */
interface FixtureLoaderInterface
{
    /**
     * Load fixtures from a yaml file.
     * It is allowed to use bundle syntax for file path:
     * e.g. @AcmeBundle/Resources/fixture.yml
     *
     * @param $path
     * @param $remove
     * @return mixed  bunle syntax
     */
    public function loadFromFile($path, $remove);

    /**
     * Load fixtures from data array
     *
     * @param array $data
     * @param $remove
     * @return void
     */
    public function load($data, $remove);
}
