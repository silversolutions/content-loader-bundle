<?php

namespace Siso\Bundle\ContentLoaderBundle\Tests\Loader;

use Siso\Bundle\ContentLoaderBundle\Loader\ContentLoader;
use Siso\Bundle\ContentLoaderBundle\Loader\NodeVisitorCollection;

class ContentLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $visitResult = [];
        $visitorCollection = $this->getVisitorCollection($visitResult);
        $loader = new ContentLoader($visitorCollection);

        $loader->load($this->getTestedData());
        $this->assertTrue(count($visitResult) == 3);
    }

    private function getTestedData()
    {
        return [
            'content' => [
                'products' => [
                    'content_type' => 'article',
                    'fields' => [
                        'name' => [
                            'eng-EN' => 'Article1',
                            'ger-DE' => 'Article1'
                        ],
                        'intro' => [
                            'eng-EN' => 'Intro1',
                            'ger-DE' => 'Intro1'
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getVisitorCollection(&$visitResult)
    {
        $visitResultCallback = function ($node, $data) use (&$visitResult) {
            $visitResult[] = $node->getName();
        };

        $visitor1 = $this->getMockBuilder('Siso\Bundle\ContentLoaderBundle\Interfaces\NodeVisitorInterface')
            ->getMock();
        $visitor1->method('getSupportedPath')
            ->willReturn('/content/*/content_type');
        $visitor1->method('visit')
            ->will(
                $this->returnCallback($visitResultCallback)
            );

        $visitor2 = $this->getMockBuilder('Siso\Bundle\ContentLoaderBundle\Interfaces\NodeVisitorInterface')
            ->getMock();
        $visitor2->method('getSupportedPath')
            ->willReturn('/content/*/fields/*');
        $visitor2->method('visit')
            ->will(
                $this->returnCallback($visitResultCallback)
            );

        $visitorCollection = new NodeVisitorCollection();
        $visitorCollection->addVisitor($visitor1);
        $visitorCollection->addVisitor($visitor2);

        return $visitorCollection;
    }
}
