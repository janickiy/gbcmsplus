<?php

namespace mcms\common\rbac;

use mcms\common\traits\PermissionMigration;
use yii\base\Object;

/**
 * Управление сущностями RBAC
 */
class AuthItemsManager extends Object
{
    use PermissionMigration;

    const ROLE_PERMISSION_PREFIX = 'UsersCanUpdate';
}