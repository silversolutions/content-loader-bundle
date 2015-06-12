<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use eZ\Publish\API\Repository\RoleService;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;

/**
 * Loader for roles
 */
class Role extends AbstractValueObjectLoader
{
    /**
     * @var RoleService
     */
    private $roleService;

    public function __construct(RoleService $roleService) {
        $this->roleService = $roleService;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/roles/*';
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        $struct = $this->roleService->newRoleCreateStruct('');
        $this->fillValueObject($struct, $data);
        if (isset($data['policies'])) {
            foreach ($data['policies'] as $policyStruct) {
                $struct->addPolicy($policyStruct);
            }
        }

        $this->roleService->createRole($struct);
    }
}