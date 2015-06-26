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
        // @todo: process parent user group
        $userGroup = $this->userService->createUserGroup($struct, $defaultUserGroup);
        $this->saveUserGroupDataToCollection($node, $userGroup);

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

    /**
     * @param TreeNodeInterface $node
     * @param $userGroup
     */
    private function saveUserGroupDataToCollection(TreeNodeInterface $node, $userGroup)
    {
        $this->objectCollection->add('content_items', $node->getName(), $userGroup->id);
        // Add location to the location list
        if (isset($userGroup->contentInfo)) {
            $this->objectCollection->add('locations', $node->getName(), $userGroup->contentInfo->mainLocationId);
        }
    }
}