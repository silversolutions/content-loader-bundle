<?php

namespace Siso\Bundle\ContentLoaderBundle\ValueObject;

use Symfony\Component\Serializer\Serializer;

/**
 * Diff for value objects
 */
class Diff
{
    /**
     * @var Serializer
     */
    private $serilaizer;

    function __construct()
    {
        $normalizer = new Normalizer();
        $this->serilaizer = new Serializer([$normalizer]);
    }

    /**
     * Compares given value object with value object data structure
     *
     * Returns set of value object attribute names. E.g.:
     *
     * [
     *      // Items that are missing in value object
     *      'add'    => ['field1', 'field2', ...]
     *      // Items that shuld not be present in value object
     *      'remove' => ['field3', 'field4', ...]
     * ]
     *
     * @param object $object
     * @param array $comparedData
     * @param string $propertyIdentifier
     * @return array
     */
    public function diff($object, $comparedData, $propertyIdentifier)
    {
        $objectData = $this->serilaizer->normalize($object);
        $comparingColumn = $this->getAttributesOfValueObjects($comparedData, $propertyIdentifier);

        $toAdd = array_diff(
            $comparingColumn,
            array_column($objectData, $propertyIdentifier)
        );

        $toRemove = array_diff(
            array_column($objectData, $propertyIdentifier),
            $comparingColumn
        );

        // @todo: add items to be updated

        return ['add' => $toAdd, 'remove' => $toRemove];
    }

    /**
     * Returns the values from a single attribute of
     * the input array of value objects
     *
     * @param array $data
     * @param string $columnName
     * @return array
     */
    private function getAttributesOfValueObjects(&$data, $columnName)
    {
        $columnValues = [];
        foreach ($data as $item) {
            $columnValues[] = $item->$columnName;
        }

        return $columnValues;
    }

}