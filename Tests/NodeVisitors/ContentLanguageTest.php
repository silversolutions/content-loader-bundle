<?php

namespace Siso\Bundle\ContentLoaderBundle\Tests\NodeVisitors;

use Siso\Bundle\ContentLoaderBundle\NodeVisitors\ContentLanguage;
use Siso\Bundle\ContentLoaderBundle\Tests\TestCases\RepositoryTestCase;
use Siso\Bundle\ContentLoaderBundle\Tree\Node;

class ContentLanguageTest extends RepositoryTestCase
{
    public function testVisit()
    {
        // One language and one language struct must be created
        $testParameters = [
            'create_content_language_call' => $this->once(),
            'new_content_language_create_struct_call' => $this->once()
        ];

        $contentLanguageLoader = new ContentLanguage($this->getContentLanguageServiceMock($testParameters));

        $node = new Node('english-us', null);
        $data = [
            'language_code' => 'eng-US',
            'name' => 'English US'
        ];

        $contentLanguageLoader->visit($node, $data);
    }
}
