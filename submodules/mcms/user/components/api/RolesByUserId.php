<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\Module;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use \mcms\user\models\User as UserModel;
use yii\helpers\ArrayHelper;

/**
 * Class RolesByUserId
 * @package mcms\user\components\api
 */
class RolesByUserId extends ApiResult
{
    /** @var  \mcms\user\models\User */
    protected $user;

    private static $_users = [];

    function init($params = [])
    {
        $userId = ArrayHelper::getValue($params, 'userId');

        if (!$userId) throw new Exception('userId is undefined');

        if (!isset(self::$_users[$userId])) {
            self::$_users[$userId] = UserModel::findOne(['id' => $userId]);
        }

        $this->user = self::$_users[$userId];

        if (!$this->user) throw new Exception('user not found');

        $this->setDataProvider(new ActiveDataProvider([
            'query' => $this->user->getRoles(),
        ]));
    }

    public function isRoot()
    {
        return $this->user->hasRole(Module::ROOT_ROLE);
    }

    public function isAdmin()
    {
        return $this->user->hasRole(Module::ADMIN_ROLE);
    }

    public function isReseller()
    {
        return $this->user->hasRole(Module::RESELLER_ROLE);
    }

    public function isPartner()
    {
        return $this->user->hasRole(Module::PARTNER_ROLE);
    }
}