<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use Siso\Bundle\ContentLoaderBundle\Interfaces\NodeVisitorInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Loader for policy limitation values
 */
class PolicyLimitationValues implements NodeVisitorInterface
{
    /**
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/roles/*/policies/*/limitations/*/limitation_values/*';
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        return (string)crc32($node->getValue());
    }
}