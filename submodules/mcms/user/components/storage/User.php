<?php

namespace mcms\user\components\storage;

use mcms\common\storage\StorageInterface;
use mcms\common\traits\StorageTrait;
use mcms\user\models\Role;
use mcms\user\models\User as UserModel;

class User implements StorageInterface, UserInterface
{

    use StorageTrait;

    /**
     * User constructor.
     * @param UserModel $userModel
     */
    public function __construct(UserModel $userModel)
    {
        $this->_model = $userModel;
    }

    public function finByRoles(array $roles)
    {
        /** @var Role[] $roles */
        $users = [];
        if (count($roles)) foreach ($roles as $role) {
            if (!($role instanceof Role)) continue;
            foreach ($role->getUsers() as $user) {
                $users[] = $user;
            }
        }

        return $users;
    }


}