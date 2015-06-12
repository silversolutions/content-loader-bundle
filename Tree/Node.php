<?php

namespace Siso\Bundle\ContentLoaderBundle\Tree;

use Iterator;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use Siso\Bundle\ContentLoaderBundle\Traits\ObjectIteratorTrait;

/**
 * Tree node
 */
class Node implements Iterator, TreeNodeInterface
{
    use ObjectIteratorTrait;

    /**
     * @var TreeNodeInterface|null
     */
    private $parent = null;

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * Node depth in the tree
     * @var int
     */
    private $depth = 0;

    /**
     * Node path in the tree
     * @var string
     */
    private $path = '/';


    /**
     * Creates a new node
     *
     * @param string $name
     * @param TreeNodeInterface|null $parent
     */
    function __construct($name, $parent)
    {
        $this->parent = $parent;
        $this->name = $name;

        if($parent) {
            $this->depth = $parent->getDepth() + 1;
            $parentPath = $parent->getPath() != '/' ? $parent->getPath() : '';
            $this->path = $parentPath . '/' . $this->getName();
        }
    }


    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function getChildByName($name)
    {
        if(!isset($this->items[$name])) {
            return null;
        }
        return $this->items[$name];
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setValue(&$value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getIndex()
    {
        if($this->getParent() == null) {
            return 0;
        }
        $parentKeys = array_keys($this->getParent()->getChildren());
        return array_search($this->getName(), $parentKeys);
    }

    /**
     * @inheritdoc
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @inheritdoc
     */
    public function hasChildren()
    {
        return count($this->items) !== 0;
    }

    /**
     * @inheritdoc
     */
    public function addChild(TreeNodeInterface $child)
    {
        $this->items[$child->getName()] = $child;
    }

    /**
     * @inheritdoc
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    public function isPathMatched($xpath)
    {
        $xpathMatcher = new XPathMatcher();
        return $xpathMatcher->matches($this, $xpath);
    }
}