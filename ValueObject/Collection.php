<?php

namespace Siso\Bundle\ContentLoaderBundle\ValueObject;

use Siso\Bundle\ContentLoaderBundle\Interfaces\ValueObjectCollectionInterface;

/**
 * Value object collection
 */
class Collection implements ValueObjectCollectionInterface
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @inheritdoc
     */
    public function add($groupName, $name, $valueObject)
    {
        $this->items[$groupName][$name] = $valueObject;
    }

    /**
     * @inheritdoc
     */
    public function getList($groupName, $names)
    {
        $result = [];
        foreach ($this->items[$groupName] as $name => $valueObject) {
            if (in_array($name, $names)) {
                $result[] = $valueObject;
            }
        }

        return $result;
    }
}