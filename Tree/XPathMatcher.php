<?php

namespace Siso\Bundle\ContentLoaderBundle\Tree;


use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use Siso\Bundle\ContentLoaderBundle\Tree\Exceptions\XPathMatcherException;

class XPathMatcher
{
    private $pattern;

    public function __construct()
    {
        $this->pattern = $this->getXpathNodePattern();
    }


    /**
     * @param TreeNodeInterface $node
     * @param string $xpathExpression
     * @return bool
     *
     * @throws XPathMatcherException
     */
    public function matches(TreeNodeInterface $node, $xpathExpression)
    {
        $xpathExpressionItems = array_reverse(explode('/', $xpathExpression));
        $xpathExpressionItems = array_filter($xpathExpressionItems);

        // Add root element forcibly
        array_push($xpathExpressionItems, '/');

        /** @var TreeNodeInterface $testedNode */
        $testedNode = $node;
        foreach ($xpathExpressionItems as $token) {

            if($testedNode == null) {
                return false;
            }

            // Check xpath expression for root xpath element
            if ($token == '/') {
                // If it ia also root of node subtree then matching is over and this node matches
                // otherwise this node doesn't match (there are more nodes in the subtree)
                return ($testedNode->getParent() == null);
            }

//            if($testedNode->getParent() === null) {
//                // XPath doesn't match given node path
//                return false;
//            }


            if (!preg_match_all($this->pattern, $token, $matches)) {
                throw new XPathMatcherException('Invalid xpath item syntax: '.$token);
            }

            $nodeName = $matches[1][0];
            if (!in_array($nodeName, ['*', $testedNode->getName()])) {
                // Node name doesn't match
                return false;
            }

            $hasSelectors = ($matches[3][0] !== '');
            if ($hasSelectors) {
                $childNodeName = $matches[3][0];
                if (!$testedNode->getChildByName($childNodeName)) {
                    // Selector doesn't match
                    return false;
                }

                // if selector is specified with operator and value
                if($matches[4][0] !== '') {
                    $childNode = $testedNode->getChildByName($childNodeName);

                    $value = $matches[6][0];

                    // Only equality operator is supported
                    if($childNode->getValue() !== $value) {
                        return false;
                    }
                }
            }


            $testedNode = $testedNode->getParent();
        }

        return true;
    }

    /**
     * @return string
     */
    private function getXpathNodePattern()
    {
        $nodeNamePattern = '[a-zA-Z0-9_\-\.]';
        $operatorPattern = '=';
        $nodeValuePattern = $nodeNamePattern;

        $pattern = sprintf(
            '#^(%s*|\*)(\[(%s*)((%s)(%s*))?\])*$#',
            $nodeNamePattern,
            $nodeNamePattern,
            $operatorPattern,
            $nodeValuePattern
        );

        return $pattern;
    }
}