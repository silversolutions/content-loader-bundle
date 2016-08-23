<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors\Fields;

use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Loader for ezxmltext field
 */
class CheckboxField extends AbstractFieldLoader
{
    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {

        if($this->getContentTypeIdentifier($node) !== 'ezboolean') {
            return null;
        }

        return (boolean) $node->getValue();

    }
}