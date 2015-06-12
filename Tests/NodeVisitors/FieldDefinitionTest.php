<?php

namespace Siso\Bundle\ContentLoaderBundle\Tests\NodeVisitors;

use Siso\Bundle\ContentLoaderBundle\NodeVisitors\FieldDefinition;
use Siso\Bundle\ContentLoaderBundle\Tests\TestCases\RepositoryTestCase;
use Siso\Bundle\ContentLoaderBundle\Tree\Node;

class FieldDefinitionTest extends RepositoryTestCase
{
    public function testVisit()
    {
        // One field definition struct must be created
        $testParameters = [
            'new_field_definition_create_struct_call' => $this->once()
        ];

        $fieldDefinitionLoader = new FieldDefinition(
            $this->getContentTypeServiceMock($testParameters)
        );

        $node = new Node('name', null);
        $data = [
            'identifier' => 'name',
            'field_type_identifier' => 'ezstring',
            'is_translatable' => true,
            'is_required' => true,
            'is_searchable' => true,
            'names' => ['eng-US' => 'Name'],
            'this_attribute_is_ignored' => 0,
        ];

        $struct = $fieldDefinitionLoader->visit($node, $data);
        $this->assertEquals($struct->identifier, 'name');
        $this->assertEquals($struct->fieldTypeIdentifier, 'ezstring');
        $this->assertEquals($struct->isRequired, true);
        $this->assertEquals($struct->isSearchable, true);
        $this->assertEquals($struct->position, 1);
        $this->assertEquals($struct->names, ['eng-US' => 'Name']);
        $this->assertEquals($struct->this_attribute_is_ignored, null);
    }
}
