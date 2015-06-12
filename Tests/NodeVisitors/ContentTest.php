<?php

namespace Siso\Bundle\ContentLoaderBundle\Tests\NodeVisitors;

use Siso\Bundle\ContentLoaderBundle\NodeVisitors\Content;
use Siso\Bundle\ContentLoaderBundle\Tests\TestCases\RepositoryTestCase;
use Siso\Bundle\ContentLoaderBundle\Tree\Node;

class ContentTest extends RepositoryTestCase
{
    public function testVisit()
    {
        // One location and one content struct must be created
        $testParameters = [
            'new_content_create_struct_call' => $this->once(),
            'new_location_create_struct_call' => $this->once()
        ];

        $contentLoader = new Content(
            $this->getContentServiceMock($testParameters),
            $this->getLocationServiceMock($testParameters),
            $this->getContentTypeServiceMock(['loaded_content_type_kind' => 'empty'])
        );

        $node = new Node('some article', null);
        $data = [
            'content_type' => 'article',
            'fields' => [
                'name' => [
                    'eng-US' => 'Products',
                    'ger-DE' => 'Produkte'
                ]
            ]
        ];

        $contentLoader->visit($node, $data);
    }
}
