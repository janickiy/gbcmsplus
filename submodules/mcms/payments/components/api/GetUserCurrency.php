<?php

namespace mcms\payments\components\api;


use mcms\common\module\api\ApiResult;
use mcms\payments\models\UserPaymentSetting;
use yii\helpers\ArrayHelper;

class GetUserCurrency extends ApiResult
{
  private $userId;
  private $currency;

  function init($params = [])
  {
    if (!$this->userId = ArrayHelper::getValue($params, 'userId')) {
      $this->addError('userId required');
    }
  }

  public function getResult()
  {
    /** @var $userSettings UserPaymentSetting */
    $userSettings = UserPaymentSetting::fetch($this->userId);
    return $userSettings->currency;
  }
}