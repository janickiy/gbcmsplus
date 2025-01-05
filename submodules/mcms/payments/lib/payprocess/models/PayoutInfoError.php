<?php

namespace mcms\payments\lib\payprocess\models;


use Yii;

class PayoutInfoError extends BasePayoutInfo
{
  public $errorMessage;

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'errorMessage' => Yii::_t('payments.payout-info.error_message'),
    ];
  }
}