<?php

namespace mcms\payments\components\api;


use mcms\common\module\api\ApiResult;
use yii\helpers\ArrayHelper;

class UserPaymentsSummary extends ApiResult
{

  private $userId;

  function init($params = [])
  {
    if (!$this->userId = ArrayHelper::getValue($params, 'userId')) {
      $this->addError('userId required');
    }
  }

  public function getResult()
  {
    if ($this->getErrors()) {
      return false;
    }
    return new \mcms\payments\models\UserPaymentsSummary(['userId' => $this->userId]);
  }
}