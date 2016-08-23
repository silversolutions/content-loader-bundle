<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use Siso\Bundle\ContentLoaderBundle\Interfaces\ValueObjectCollectionInterface;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;

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
    /**
     * @var  \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $dbConnection;

    public function __construct(
            Repository $repository,
            ValueObjectCollectionInterface $objectStorage,
            DatabaseHandler $dbConnection
     )
    {
        parent::__construct($repository);
        $this->userService = $this->repository->getUserService();
        $this->contentTypeService = $this->repository->getContentTypeService();
        $this->objectCollection = $objectStorage;
        $this->dbConnection = $dbConnection;
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

            if (isset($data['parentContentId'])) {
                echo "getusergroup by parentid ".$data['parentContentId']."\n";
                $userGroups = $this->getParentGroupsByLocationId($data['parentContentId']);
            } else {
                $userGroups = $this->getParentGroups($data);
            }


            $user = $this->userService->createUser($userStruct, $userGroups);

        } else {
            $user = $this->userService->loadUserByLogin($data['login']);
            // @todo: update of user object is not implemented
        }
        if (isset($data['passwordhash']) && !empty($data['passwordhash']) && $user->id > 0) {
            echo "Set password hash ";
            $this->setPasswordhash($user->id, $data['passwordhash']);
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

    private function getParentGroupsByLocationId($userGroupId) {

        $userGroups = [];
        $userGroups[] = $this->userService->loadUserGroup($userGroupId);
        return $userGroups;
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

    /**
     * Update the password hash in the database
     * @param $userId
     * @param $passwordhash
     * @return boolean
     */
    private function setPasswordhash($userId, $passwordhash)
    {
        try {
            $db = $this->dbConnection;
            $statement = $db->prepare(
                sprintf('UPDATE ezuser
                    set password_hash = \'%s\'
                    WHERE contentobject_id=%d;',
                            $passwordhash,
                            $userId
                            )
                        );

            $statement->execute();
        } catch (\Exception $e) {
            echo "Exception setPasswordhash($userId, $passwordhash): ".$e->getMessage();
            return false;
        }
        return true;
    }
}