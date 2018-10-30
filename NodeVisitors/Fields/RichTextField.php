<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors\Fields;

use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Loader for ezxmltext field
 */
class RichTextField extends AbstractFieldLoader
{
    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        if($this->getContentTypeIdentifier($node) !== 'ezrichrichtext') {
            return null;
        }
        if (substr($node->getValue(),0,8) == '<section') {
            return $node->getValue();
        }
        return '<section xmlns="http://ez.no/namespaces/ezpublish5/xhtml5/edit">' .

            $node->getValue() .
            '</section>';
    }
}