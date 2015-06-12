<?php

namespace Siso\Bundle\ContentLoaderBundle\Tests\NodeVisitors;

use Siso\Bundle\ContentLoaderBundle\NodeVisitors\ContentType;
use Siso\Bundle\ContentLoaderBundle\Tests\TestCases\RepositoryTestCase;
use Siso\Bundle\ContentLoaderBundle\Tree\Node;

class ContentTypeTest extends RepositoryTestCase
{
    public function testVisitForNewContentType()
    {
        // One content type that doesn't exist yet
        $testParameters = [
            'loaded_content_type_kind' => 'new',
            'create_content_type_call' => $this->once()
        ];

        $contentLoader = new ContentType(
            $this->getContentTypeServiceMock($testParameters),
            $this->getMock('Siso\Bundle\ContentLoaderBundle\ValueObject\Diff')
        );

        $node = new Node('article', null);
        $data = [
            'identifier' => 'article',
            'names' => ['eng-US' => 'Article'],
            'name_schema' => '<name>',
            'field_definitions' => []
        ];

        $contentLoader->visit($node, $data);
    }
}
