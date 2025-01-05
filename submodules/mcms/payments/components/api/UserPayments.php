<?php

namespace mcms\payments\components\api;

use mcms\common\module\api\ApiResult;
use mcms\payments\models\UserPayment;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class UserPayments extends ApiResult
{

  private $userId;

  function init($params = [])
  {
    if (!$this->userId = ArrayHelper::getValue($params, 'userId')) {
      $this->addError('userId required');
    }

    $this->setDataProvider(new ActiveDataProvider([
      'query' => UserPayment::UserPaymentsInvoices($this->userId),
      'sort' => false
    ]));

    $this->setResultTypeDataProvider();
  }

  /**
   * Есть ли у партнера незавершенные выплаты
   * @param string $currency
   * @return bool
   */
  public function hasAwaitingPayments($currency)
  {
    return UserPayment::hasAwaitingPayments($this->userId, $currency);
  }
}