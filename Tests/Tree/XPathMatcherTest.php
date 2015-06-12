<?php
/**
 * Product silver.e-shop
 *
 * A powerful e-commerce solution for B2B online shops / portals and complex
 * online applications that have access to ERP data, usually in real time.
 * http://www.silversolutions.de/eng/Products/silver.e-shop
 *
 * This file contains the class XPathTest.php
 *
 * @copyright Copyright (C) 2013 silver.solutions GmbH. All rights reserved.
 * @license see vendor/silversolutions/silver.e-shop/license_txt_ger.pdf
 * @version
 * @package silver.e-shop
 */

namespace Siso\Bundle\ContentLoaderBundle\Tests\Tree;

use Siso\Bundle\ContentLoaderBundle\Tree\Tree;
use Siso\Bundle\ContentLoaderBundle\Tree\XPathMatcher;

class XPathMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatches()
    {
        $data = [
            'a' => 'scalar',
            'b' => ['b1' => 'b1-value', 'b2' => 'b2-value'],
            'c' => [
                'c1' => [
                    'c1.1' => [
                        'c1.1.1' => 'c1.1.1-value',
                        'c1.1.2' => 'c1.1.2-value',
                        'c1.1.3' => 'c1.1.3-value',
                    ],
                    'c1.2' => 'c1.2-value'
                ],
                'c2' => 'c2-value'
            ],
        ];

        $tree = new Tree($data);
        $node = $tree->getRoot()->getChildByName('c')->getChildByName('c1');
        $matcher = new XPathMatcher();

        $this->assertTrue($matcher->matches($node, '/*/*'));
        $this->assertTrue($matcher->matches($node, '/c/c1[c1.2=c1.2-value]'));
        $this->assertTrue($matcher->matches($node, '/c[c2]/c1[c1.2=c1.2-value]'));
        $this->assertTrue($matcher->matches($node, '/c[c2=c2-value]/c1[c1.2=c1.2-value]'));

        $this->assertTrue($matcher->matches($node, '/c/*[c1.2=c1.2-value]'));
        $this->assertTrue($matcher->matches($node, '/*/c1[c1.2=c1.2-value]'));
        $this->assertTrue($matcher->matches($node, '/*/*[c1.2=c1.2-value]'));
        $this->assertTrue($matcher->matches($node, '/*[c2]/c1[c1.2=c1.2-value]'));
        $this->assertTrue($matcher->matches($node, '/c[c2]/*[c1.2=c1.2-value]'));
        $this->assertTrue($matcher->matches($node, '/*[c2]/*[c1.2=c1.2-value]'));

        $this->assertTrue($matcher->matches($node, '/c[c2]/c1'));
        $this->assertTrue($matcher->matches($node, '/c/c1'));
        $this->assertTrue($matcher->matches($node, '/c/c1[c1.1]'));
        $this->assertTrue($matcher->matches($node, '/*/c1'));
        $this->assertTrue($matcher->matches($node, '/c/*'));

        $this->assertFalse($matcher->matches($node, '/*'));
        $this->assertFalse($matcher->matches($node, '/*/*/*'));
        $this->assertFalse($matcher->matches($node, '/'));
        $this->assertFalse($matcher->matches($node, '/a/c1'));
        $this->assertFalse($matcher->matches($node, '/a/*'));
        $this->assertFalse($matcher->matches($node, '/b'));
    }

    /**
     * @dataProvider incorrectXPathsProvider
     * @expectedException \Siso\Bundle\ContentLoaderBundle\Tree\Exceptions\XPathMatcherException
     * @param string $xpath
     */
    public function testIncorrectXPaths($xpath)
    {
        $data = [
            'a' => [
                'b' => 'b-value'
            ]
        ];

        $tree = new Tree($data);
        $node = $tree->getRoot()->getChildByName('a')->getChildByName('b');
        $matcher = new XPathMatcher();

        $this->assertTrue($matcher->matches($node, $xpath));

    }


    /**
     * Data provider for xpath expressions with wrong syntax
     *
     * @return array
     */
    public function incorrectXPathsProvider()
    {
        return [
            ['/a/b['],
            ['/a/b]'],
            ['/a/**'],
            ['/**/b'],
            ['/**/**'],
        ];
    }
}
