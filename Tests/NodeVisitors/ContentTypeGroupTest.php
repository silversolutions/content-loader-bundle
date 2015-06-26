<?php

namespace Siso\Bundle\ContentLoaderBundle\Tests\NodeVisitors;

use Siso\Bundle\ContentLoaderBundle\NodeVisitors\ContentType;
use Siso\Bundle\ContentLoaderBundle\Tests\TestCases\RepositoryTestCase;
use Siso\Bundle\ContentLoaderBundle\Tree\Node;

class ContentTypeGroupTest extends RepositoryTestCase
{
    public function testVisitForNewContentTypeGroup()
    {
        // One content type that doesn't exist yet
        $testParameters = [
            'loaded_content_type_kind' => 'new',
            'create_content_type_call' => $this->once()
        ];

        $contentLoader = new ContentTypeGroup(
            $this->getContentTypeServiceMock($testParameters)
        );

        $node = new Node('user', null);
        $data = [
            'identifier' => 'article',
            'names' => ['eng-US' => 'Article'],
            'name_schema' => '<name>',
            'field_definitions' => []
        ];

        $contentLoader->visit($node, $data);
    }
}
