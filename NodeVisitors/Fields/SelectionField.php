<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors\Fields;

use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use eZ\Publish\Core\FieldType\Selection\Type as eZSelectionType;

/**
 * Loader for ezxmltext field
 */
class SelectionField extends AbstractFieldLoader
{
    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {

        if($this->getContentTypeIdentifier($node) !== 'ezselection') {
            return null;
        }
        $selectionType = new eZSelectionType();
        return $selectionType->fromHash(array($node->getValue()));

    }
}