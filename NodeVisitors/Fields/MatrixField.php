<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors\Fields;

use EzSystems\MatrixBundle\FieldType\Matrix\Type as MatrixType;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Loader for ezmatrix field
 */
class MatrixField extends AbstractFieldLoader
{
    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        if($this->getContentTypeIdentifier($node) !== 'ezmatrix') {
            return null;
        }

        $type = new MatrixType();
        return $type->fromHash($data);
    }
}