<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use eZ\Publish\API\Repository\ContentTypeService;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Loader for content field definitions of content type
 */
class FieldDefinition extends AbstractValueObjectLoader
{
    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }
    /**
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/content_types/*/field_definitions/*';
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        $struct = $this->contentTypeService->newFieldDefinitionCreateStruct('', '');
        $this->fillValueObject($struct, $data);
        // Get position from node index (starts from 1)
        $struct->position = $node->getIndex() + 1;

        return $struct;
    }
}