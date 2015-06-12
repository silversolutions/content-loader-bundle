<?php

namespace Siso\Bundle\ContentLoaderBundle\Loader;

use Siso\Bundle\ContentLoaderBundle\Interfaces\NodeVisitorCollectionInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\NodeVisitorInterface;
use Siso\Bundle\ContentLoaderBundle\Tree\Node;

class NodeVisitorCollection implements NodeVisitorCollectionInterface
{
    /**
     * @var NodeVisitorInterface[]
     */
    private $visitors = [];

    /**
     * Adds visitor.
     *
     * @param NodeVisitorInterface $visitor
     */
    public function addVisitor(NodeVisitorInterface $visitor)
    {
        $this->visitors[$visitor->getSupportedPath()][] = $visitor;
    }

    /**
     * @inheritdoc
     */
    public function getVisitors(Node $node)
    {
        $visitors = [];
        foreach ($this->visitors as $visitorPath => $list) {
            if ($node->isPathMatched($visitorPath)) {
                $visitors = array_merge($visitors, $list);
            }
        }

        return $visitors;
    }
}