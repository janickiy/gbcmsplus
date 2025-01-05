<?php

namespace mcms\partners\models;

use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\base\Model;

class EarlyPaymentRequestForm extends Model
{
  /** @var array[] Заказываемые выплаты в формате [0 => [user_wallet_id => int ID кошелька, invoice_amount => number Сумма]] */
  public $payments;
  public $balance;
  /** @var number[] Суммы выплат. Специально для валидации. Заполняется автоматически */
  public $paymentsValidate;

  public function beforeValidate()
  {
    $this->paymentsValidate = ArrayHelper::getColumn($this->payments, 'invoice_amount');
    return parent::beforeValidate();
  }

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return [
      [['payments', 'balance'], 'required'],
      [['balance'], 'double'],
      ['paymentsValidate', 'each', 'rule' => ['double']],
      ['paymentsValidate', 'each', 'rule' => ['compare', 'compareValue' => 0, 'operator' => '>']],
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'balance' => Yii::_t('payments.payments-balance-main'),
    ];
  }

  /**
   * Заказываемые выплаты
   * @return array[]|bool
   */
  public function getPaymentRequests()
  {
    return $this->payments;
  }
}