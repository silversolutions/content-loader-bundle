<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\ValueObjectCollectionInterface;

/**
 * Loader for users
 */
class User extends AbstractContentLoader
{
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var ContentTypeService
     */
    private $contentTypeService;
    /**
     * @var ValueObjectCollectionInterface
     */
    private $objectCollection;

    public function __construct(Repository $repository, ValueObjectCollectionInterface $objectStorage)
    {
        parent::__construct($repository);
        $this->userService = $this->repository->getUserService();
        $this->contentTypeService = $this->repository->getContentTypeService();
        $this->objectCollection = $objectStorage;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/content/*[content_type=user]';
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        if(!is_array($data)) {
            return null;
        }

        if (!$this->isExistingUser($data['login'])) {
            $userStruct = $this->getUserStruct($data);
            $userGroups = $this->getParentGroups($data);
            $user = $this->userService->createUser($userStruct, $userGroups);
        } else {
            $user = $this->userService->loadUserByLogin($data['login']);
            // @todo: update of user object is not implemented
        }

        if (isset($data['roles'])) {
            foreach ($data['roles'] as $roleId) {
                $role = $this->repository->getRoleService()->loadRoleByIdentifier($roleId);
                // @doc: parameter RoleLimitation is not supported.
                // Not possible to assign role to user or group with limitation
                $this->repository->getRoleService()->assignRoleToUser($role, $user);
            }
        }

        return $user;
    }

    /**
     * Returns true if user with given login name exists
     *
     * @param string $login
     * @return bool
     */
    private function isExistingUser($login)
    {
        try {
            $this->userService->loadUserByLogin($login);

            return true;
        } catch (NotFoundException $exception) {
            return false;
        }
    }

    /**
     * Creates and prepares user structure
     *
     * @param array $data
     * @return \eZ\Publish\API\Repository\Values\User\UserCreateStruct
     */
    private function getUserStruct(array &$data)
    {
        $struct = $this->userService->newUserCreateStruct('', '', '', '');
        $this->fillValueObject($struct, $data, ['content_type']);

        return $struct;
    }

    /**
     * @param $data
     * @return array
     */
    private function getParentGroups(&$data)
    {
        $userGroupIds = $this->objectCollection->getList('content_items', $data['groups']);
        $userGroups = [];
        foreach ($userGroupIds as $userGroupId) {
            $userGroups[] = $this->userService->loadUserGroup($userGroupId);
        }

        return $userGroups;
    }
}