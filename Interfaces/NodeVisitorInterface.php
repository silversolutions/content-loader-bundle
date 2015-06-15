<?php

namespace Siso\Bundle\ContentLoaderBundle\Interfaces;

/**
 * Interface for tree node visitors
 */
interface NodeVisitorInterface
{
    /**
     * Returns path of a tree node, that this visitor can visit.
     *
     * @return string
     */
    public function getSupportedPath();

    /**
     * Visits a tree node
     *
     * @param TreeNodeInterface $node
     * @param array $data
     * @return mixed
     */
    public function visit(TreeNodeInterface $node, &$data);
}
