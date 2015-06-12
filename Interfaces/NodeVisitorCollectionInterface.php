<?php

namespace Siso\Bundle\ContentLoaderBundle\Interfaces;

use Siso\Bundle\ContentLoaderBundle\Tree\Node;

/**
 * Interface for a readonly access to a visitor collection
 */
interface NodeVisitorCollectionInterface
{
    /**
     * Gets all visitors that can visit given node
     *
     * @param Node $node
     * @return NodeVisitorInterface[]
     */
    public function getVisitors(Node $node);
}