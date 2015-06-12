<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use eZ\Publish\API\Repository\Repository;
use Siso\Bundle\ContentLoaderBundle\Interfaces\NodeVisitorInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Abstract loader for features based on eZ Publish ValueObject.
 *
 * Applicable if particular service is able to create content using
 * value object (structs). E.g.:
 *
 * $repository->getContentService()->newContentCreateStruct($contentType, '');
 * $repository->getRoleService()->newPolicyCreateStruct('', '');
 */
abstract class AbstractValueObjectLoader implements NodeVisitorInterface
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Fills value object with given content data
     *
     * @param object $valueObject
     * @param array $data
     * @param array $excludedProperties
     */
    public function fillValueObject($valueObject, &$data, $excludedProperties = [])
    {
        foreach ($data as $property => $value) {
            $propertyName = $this->denormalizePropertyName($property);
            if (in_array($property, $excludedProperties)) {
                continue;
            }

            try {
                $valueObject->$propertyName = $value;
            } catch (\Exception $e) {
                // Ignore unsupported properties
            }
        }
    }

    /**
     * Convert snake case property name to camel case attribute name.
     * E.g. is_translated -> isTranslated
     *
     * @param string $propertyName
     * @return string
     */
    private function denormalizePropertyName($propertyName)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $propertyName))));
    }

    abstract public function visit(TreeNodeInterface $node, &$data);

    abstract public function getSupportedPath();

}