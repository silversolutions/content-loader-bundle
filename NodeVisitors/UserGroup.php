<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\ValueObjectCollectionInterface;

/**
 * Loader for user groups
 */
class UserGroup extends AbstractContentLoader
{
    /**
     * eZ Publish default user group
     */
    const DEFAULT_USER_GROUP_ID = 4;
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var ValueObjectCollectionInterface
     */
    private $objectCollection;

    public function __construct(Repository $repository, ValueObjectCollectionInterface $objectStorage)
    {
        parent::__construct($repository);
        $this->userService = $this->repository->getUserService();
        $this->objectCollection = $objectStorage;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/content/*[content_type=user_group]';
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        if(!is_array($data)) {
            return null;
        }

        $struct = $this->userService->newUserGroupCreateStruct('');
        $this->fillValueObject($struct, $data, ['content_type']);

        $defaultUserGroup = $this->userService->loadUserGroup(self::DEFAULT_USER_GROUP_ID);
        $userGroup = $this->userService->createUserGroup($struct, $defaultUserGroup);
        $this->objectCollection->add('user_groups', $node->getName(), $userGroup);

        if (isset($data['roles'])) {
            foreach ($data['roles'] as $roleId) {
                $role = $this->repository->getRoleService()->loadRoleByIdentifier($roleId);
                // @doc: parameter RoleLimitation is not supported.
                // Not possible to assign role to user or group with limitation
                $this->repository->getRoleService()->assignRoleToUserGroup($role, $userGroup);
            }
        }

        return $userGroup;
    }
}