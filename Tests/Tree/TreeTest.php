<?php

namespace Siso\Bundle\ContentLoaderBundle\Tests\Tree;

use Siso\Bundle\ContentLoaderBundle\Tree\Tree;

class TreeTest extends \PHPUnit_Framework_TestCase
{
    public function testTree()
    {
        $data = [
            'first' => 'firstValue',
            'second' => [
                'second0' => [
                    'second0-1' => 'secondO-1-value',
                    'second0-2' => 'secondO-2-value'
                ]
            ],
            'third' => 'thirdValue'
        ];

        $tree = new Tree($data);

        $node = $tree->getRoot()->getChildByName('second')->getChildByName('second0');
        $this->assertTrue($node->isPathMatched('/*/second0'));
        $this->assertTrue($node->isPathMatched('/second/*'));
        $this->assertTrue($node->isPathMatched('/*/*'));
        $this->assertFalse($node->isPathMatched('/first/*'));
    }



}
