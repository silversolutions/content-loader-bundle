<?php

namespace Siso\Bundle\ContentLoaderBundle\Tree;

use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Class for tree structures
 */
class Tree
{
    /**
     * @var Node
     */
    private $root;

    /**
     * @param $data
     */
    function __construct(&$data)
    {
        $this->root = new Node('', null);
        $this->loadChildren($this->root, $data);
    }

    /**
     * Recursibely loads children of given node
     *
     * @param TreeNodeInterface $node
     * @param array $data
     */
    private function loadChildren($node, &$data) {
        foreach ($data as $key => &$item) {
            $child = new Node($key, $node);
            $node->addChild($child);
            $child->setValue($item);

            if(is_array($item)) {
                $this->loadChildren($child, $item);
            }
        }
    }

    /**
     * @return TreeNodeInterface
     */
    public function getRoot()
    {
        return $this->root;
    }
}
