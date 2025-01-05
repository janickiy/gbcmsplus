<?php

namespace mcms\user\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;

/**
 * api getOneUser
 * Class UserList
 * @package mcms\user\components\api
 */
class UserList extends ApiResult
{
    protected $userId;


    public function init($params = [])
    {
        $this->userId = ArrayHelper::getValue($params, 'user_id', null);
        if (!$this->userId) $this->addError('user_id is not set');
    }

    public function getResult()
    {
        return \mcms\user\models\User::findOne([
            'id' => $this->userId
        ]);
    }

    public function getUrlParam()
    {
        return ['/users/users/view/', 'id' => $this->userId];
    }

}