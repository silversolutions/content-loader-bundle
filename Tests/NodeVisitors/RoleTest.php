<?php

namespace Siso\Bundle\ContentLoaderBundle\Tests\NodeVisitors;

use Siso\Bundle\ContentLoaderBundle\NodeVisitors\Role;
use Siso\Bundle\ContentLoaderBundle\Tests\TestCases\RepositoryTestCase;
use Siso\Bundle\ContentLoaderBundle\Tree\Node;

class RoleTest extends RepositoryTestCase
{
    public function testVisit()
    {
        // One role and one role struct must be created
        $testParameters = [
            'create_role_call' => $this->once(),
            'new_role_create_struct_call' => $this->once()
        ];

        $roleLoader = new Role($this->getRoleServiceMock($testParameters));

        $node = new Node('anonymous', null);
        $data = [
            'identifier' => 'anonymous',
            'policies' => []
        ];


        $roleLoader->visit($node, $data);
    }
}
