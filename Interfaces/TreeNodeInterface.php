<?php

namespace Siso\Bundle\ContentLoaderBundle\Interfaces;

/**
 * Interface for tree node
 */
interface TreeNodeInterface
{
    /**
     * Gets children
     *
     * @return TreeNodeInterface[]
     */
    public function getChildren();

    /**
     * Gets child node by name.
     * If there is no node with a given name, returns null.
     *
     * @return TreeNodeInterface|null
     */
    public function getChildByName($name);

    /**
     * Gets parent node
     *
     * @return TreeNodeInterface|null
     */
    public function getParent();

    /**
     * Gets node value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Sets node value
     *
     * @param mixed $value
     */
    public function setValue(&$value);

    /**
     * Gets node name
     *
     * @return string
     */
    public function getName();

    /**
     * Gets node index in a sibling list
     *
     * @return int
     */
    public function getIndex();

    /**
     * Gets node depth in a tree
     *
     * @return int
     */
    public function getDepth();

    /**
     * Gets true if node has children, false otherwise
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * Adds child
     *
     * @param TreeNodeInterface $child
     * @return void
     */
    public function addChild(TreeNodeInterface $child);

    /**
     * Gets node path
     *
     * @return string
     */
    public function getPath();

    /**
     * Gets true if a node path matches given xpath.
     *
     * Only simplified xpath rules are supported: '*' for any element,
     * e.g. /node1/* /node2
     *
     * @param $xpath
     * @return mixed
     */
    public function isPathMatched($xpath);
}