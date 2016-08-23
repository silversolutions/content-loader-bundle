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
        if (substr($node->getValue(),0,5) == '<?xml') {
            return $node->getValue();
        }
        return '<?xml version="1.0" encoding="utf-8"?>' .
            '<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" ' .
            'xmlns:image="http://ez.no/namespaces/ezpublish3/image/" ' .
            ' xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/">' .

            $node->getValue() .
            '</section>';
    }
}