<?php

namespace Siso\Bundle\ContentLoaderBundle\Interfaces;

/**
 * Interface for value object collection
 */
interface ValueObjectCollectionInterface
{
    /**
     * Add object to a collection
     *
     * @param string $groupName
     * @param string $name
     * @param object $valueObject
     * @return void
     */
    public function add($groupName, $name, $valueObject);

    /**
     * Gets a list of objects from given group by their names
     *
     * @param string $groupName
     * @param string[] $names
     * @return array
     */
    public function getList($groupName, $names);
}