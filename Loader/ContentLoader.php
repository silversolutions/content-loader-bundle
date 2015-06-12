<?php

namespace Siso\Bundle\ContentLoaderBundle\Loader;

use Siso\Bundle\ContentLoaderBundle\Exceptions\NodeDataLoadException;
use Siso\Bundle\ContentLoaderBundle\Interfaces\ContentLoaderInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\NodeVisitorCollectionInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use Siso\Bundle\ContentLoaderBundle\Tree\Tree;

/**
 * Content loader
 */
class ContentLoader implements ContentLoaderInterface
{
    /**
     * @var NodeVisitorCollectionInterface
     */
    private $visitors;

    public function __construct(NodeVisitorCollectionInterface $visitors)
    {
        $this->visitors = $visitors;
    }

    /**
     * @inheritdoc
     */
    public function load($data, $parameters = [])
    {
        $tree = new Tree($data);
        $this->visitNodes($tree->getRoot());
    }

    /**
     * Recursively walks over all tree nodes and call visitors for them
     *
     * @param TreeNodeInterface $node
     * @return mixed
     * @throws NodeDataLoadException
     */
    private function visitNodes(TreeNodeInterface $node)
    {
        try {
            // Collects values from children
            $data = $node->getValue();
            if ($node->hasChildren()) {
                $data = [];
                foreach ($node as $key => $item) {
                    $data[$key] = $this->visitNodes($item);
                }
            }

            // Call visitors for the current node and data collected from children
            $visitors = $this->visitors->getVisitors($node);
            foreach ($visitors as $visitor) {
                $visitResult = $visitor->visit($node, $data);
                if ($visitResult !== null) {
                    $data = $visitResult;
                }
            }

            return $data;
        } catch (\Exception $exception) {
            throw new NodeDataLoadException('Error loading data from '.$node->getPath(), $exception);
        }
    }
}