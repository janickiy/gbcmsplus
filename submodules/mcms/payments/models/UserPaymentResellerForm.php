<?php

namespace mcms\payments\models;

use mcms\common\exceptions\ModelNotSavedException;
use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Wallet;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class UserPaymentResellerForm
 * @package mcms\payments\models
 *
 */
class UserPaymentResellerForm extends UserPaymentForm
{
  public function beforeValidate()
  {
    $this->processing_type = self::PROCESSING_TYPE_EXTERNAL;
    return parent::beforeValidate();
  }

  public function checkAmount()
  {
    if (!$this->isNewRecord || !$this->user_id || !$this->invoice_currency) return false;

    $balance = $this->getUserBalance($this->invoice_currency);
    $selectedBalance = $balance->getResellerBalance();

    if ($selectedBalance <= 0) {
      $this->addError('invoice_amount', Yii::_t('payments.user-payments.error-balance-main'));
      return false;
    }
    if ($selectedBalance - $this->invoice_amount < 0) {
      $this->addError('invoice_amount', Yii::_t('payments.user-payments.error-balance-insufficient') . ' ' .
        Yii::$app->getFormatter()->asPrice($selectedBalance, $this->invoice_currency));
      return false;
    }
  }
}