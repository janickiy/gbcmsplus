<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\Module;
use Yii;

class PartnerCanViewPromo extends ApiResult
{
  /** @var  \yii\rbac\ManagerInterface */
  private $authManager;

  function init($params = [])
  {
    $this->authManager = Yii::$app->authManager;
  }

  public function assign($userId)
  {
    $permission = $this->authManager->getPermission(Module::PERMISSION_CAN_PARTNER_VIEW_PROMO);
    if (!$this->authManager->getAssignment(Module::PERMISSION_CAN_PARTNER_VIEW_PROMO, $userId)) {
      $this->authManager->assign($permission, $userId);
    }
  }
  public function revoke($userId)
  {
    $permission = $this->authManager->getPermission(Module::PERMISSION_CAN_PARTNER_VIEW_PROMO);
    $this->authManager->revoke($permission, $userId);
  }
  public function isPromoHidden($userId)
  {
    return Module::getInstance()->getSettingsResellerCanHidePromo()
      && $this->authManager->checkAccess($userId, Module::PERMISSION_CAN_PARTNER_VIEW_PROMO)
      ;
  }

}