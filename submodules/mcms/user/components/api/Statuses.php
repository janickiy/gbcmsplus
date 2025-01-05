<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\models\User as UserModel;

class Statuses extends ApiResult
{
    const STATUS_DELETED = UserModel::STATUS_DELETED;
    const STATUS_BLOCKED = UserModel::STATUS_BLOCKED;
    const STATUS_INACTIVE = UserModel::STATUS_INACTIVE;
    const STATUS_ACTIVE = UserModel::STATUS_ACTIVE;
    const STATUS_ACTIVATION_WAIT_HAND = UserModel::STATUS_ACTIVATION_WAIT_HAND;
    const STATUS_ACTIVATION_WAIT_EMAIL = UserModel::STATUS_ACTIVATION_WAIT_EMAIL;
    const STATUS_REMEMBER_PASSWORD_BY_HAND = UserModel::STATUS_REMEMBER_PASSWORD_BY_HAND;

    function init($params = [])
    {
        return;
    }

    public function getAvailableStatuses()
    {
        return UserModel::availableStatuses;
    }
}