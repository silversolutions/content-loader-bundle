<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors\Fields;

use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Loader for ezxmltext field
 */
class XmlTextField extends AbstractFieldLoader
{
    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        if($this->getContentTypeIdentifier($node) !== 'ezxmltext') {
            return null;
        }
        return '<?xml version="1.0" encoding="utf-8"?>' .
            '<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"' .
            'xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"' .
            'xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"' .
            'xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">' .
            $node->getValue() .
            '</section>';
    }
}