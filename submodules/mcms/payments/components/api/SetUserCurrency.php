<?php

namespace mcms\payments\components\api;


use mcms\common\module\api\ApiResult;
use mcms\payments\models\UserPaymentSetting;
use yii\helpers\ArrayHelper;

class SetUserCurrency extends ApiResult
{
  private $userId;
  private $currency;

  function init($params = [])
  {
    if (!$this->userId = ArrayHelper::getValue($params, 'userId')) {
      $this->addError('userId required');
    }
    if (!$this->currency = ArrayHelper::getValue($params, 'currency')) {
      $this->addError('currency required');
    }
  }

  public function getResult()
  {
    if ($this->getErrors()) {
      return false;
    }
    /** @var $userSettings UserPaymentSetting */
    if (!$userSettings = UserPaymentSetting::findOne(['user_id' => $this->userId])) {
      $userSettings = new UserPaymentSetting([
        'user_id' => $this->userId,
        'currency' => $this->currency,
        'scenario' => UserPaymentSetting::SCENARIO_ADMIN_CREATE
      ]);
    } else {
      $userSettings->setScenario(UserPaymentSetting::SCENARIO_PARTNER_UPDATE);
      $userSettings->currency = $this->currency;
    }
    if ($result = $userSettings->save()) {
      $this->addError('model save error');
    }
    return $result;
  }
}