<?php

namespace mcms\payments\components\api;


use mcms\common\module\api\ApiResult;
use mcms\payments\components\UserBalance as UserBalanceModel;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPaymentSetting;
use yii\helpers\ArrayHelper;

class BuyDomain extends ApiResult
{

  private $description;
  private $paymentAmount;
  private $userId;

  function init($params = [])
  {
    if (!$this->userId = ArrayHelper::getValue($params, 'userId')) {
      $this->addError('userId required');
    }
    if (!$this->paymentAmount = ArrayHelper::getValue($params, 'paymentAmount')) {
      $this->addError('paymentAmount required');
    }
    $this->description = ArrayHelper::getValue($params, 'description');
  }

  public function getResult()
  {
    if ($this->getErrors()) {
      return false;
    }
    $balance = new UserBalanceModel(['userId' => $this->userId]);
    if ($balance->getMain() - $this->paymentAmount <= 0) {
      $this->adderror('Insufficient funds on the account');
      return false;
    }

    $invoice = new UserBalanceInvoice([
      'user_id' => $this->userId,
      'currency' => $balance->getCurrency(),
      'amount' => -1 * abs($this->paymentAmount),
      'description' => $this->description,
      'type' => UserBalanceInvoice::TYPE_BUY_DOMAIN,
    ]);
    return $invoice->save();
  }
}