<?php

namespace mcms\payments\lib\payprocess\models;

use Yii;

class YandexMoneyPayoutInfo extends BasePayoutInfo
{
  public $operationId;
  public $status;
  public $amount;
  public $amountDue;
  public $fee;
  public $datetime;
  public $title;
  public $recipient;
  public $message;
  public $description;
  public $error;

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'operationId' => Yii::_t('payments.payout-info.operationId'),
      'status' => Yii::_t('payments.payout-info.status'),
      'amount' => Yii::_t('payments.payout-info.amount'),
      'amountDue' => Yii::_t('payments.payout-info.amountDue'),
      'fee' => Yii::_t('payments.payout-info.fee'),
      'datetime' => Yii::_t('payments.payout-info.datetime'),
      'title' => Yii::_t('payments.payout-info.title'),
      'recipient' => Yii::_t('payments.payout-info.recipient'),
      'message' => Yii::_t('payments.payout-info.message'),
      'description' => Yii::_t('payments.payments.description'),
      'error' => Yii::_t('payments.payout-info.error'),
    ];
  }
}