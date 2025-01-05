<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\components\widgets\UserBackWidget;
use mcms\user\Module;
use Yii;

class UserBack extends ApiResult
{
    private $params;

    function init($params = [])
    {
        $this->params = $params;
    }

    public function getResult()
    {
        $this->prepareWidget(UserBackWidget::class, $this->params);
        return parent::getResult();
    }

    public function hasOriginalLoggedInUserRoleAdminRootReseller()
    {
        $backIdentity = Yii::$app->session->get(\mcms\user\models\User::SESSION_BACK_IDENTITY_ID);
        $originalLoggedInUserId = is_array($backIdentity) ? array_pop($backIdentity) : $backIdentity;
        if ($originalLoggedInUserId === null) return false;

        $roles = array_flip([Module::ADMIN_ROLE, Module::RESELLER_ROLE, Module::ROOT_ROLE]);
        $userRoles = Yii::$app->authManager->getRolesByUser($originalLoggedInUserId);

        return count(array_intersect_key($userRoles, $roles)) > 0;
    }
}