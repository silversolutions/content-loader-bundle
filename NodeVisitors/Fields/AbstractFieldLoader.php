<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors\Fields;

use eZ\Publish\API\Repository\Repository;
use Siso\Bundle\ContentLoaderBundle\Interfaces\NodeVisitorInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Abstract loader for field values, e.g. xml field, matrix field.
 */
abstract class AbstractFieldLoader implements NodeVisitorInterface
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
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/content/*/fields/*/*';
    }

    /**
     * @inheritdoc
     */
    abstract public function visit(TreeNodeInterface $node, &$data);

    /**
     * Gets content type identifier of field corresponding with the given node
     *
     * @param TreeNodeInterface $node
     * @return string
     */
    public function getContentTypeIdentifier(TreeNodeInterface $node)
    {
        $parent = $node->getParent();
        $fieldName = $parent->getName();
        $grandPa = $parent->getParent()->getParent();

        $contentTypeNode = $grandPa->getChildByName('content_type');

        $contentType = $this->repository->getContentTypeService()->loadContentTypeByIdentifier(
            $contentTypeNode->getValue()
        );
        $fieldDefinition = $contentType->getFieldDefinition($fieldName);

        return $fieldDefinition->fieldTypeIdentifier;
    }
}