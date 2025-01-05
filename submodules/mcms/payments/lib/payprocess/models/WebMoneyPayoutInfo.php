<?php

namespace mcms\payments\lib\payprocess\models;

use Yii;

/**
 * Class WebMoneyPayoutInfo
 * @package mcms\payments\lib\payprocess\models
 */
class WebMoneyPayoutInfo extends BasePayoutInfo
{
  public $idTransaction;
  public $payeePurse;
  public $payerPurse;
  public $resultAmount;
  public $dateCreated;
  public $dateUpdated;
  public $description;

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'idTransaction' => Yii::_t('payments.payout-info.idTransaction'),
      'payeePurse' => Yii::_t('payments.payout-info.payeePurse'),
      'payerPurse' => Yii::_t('payments.payout-info.payerPurse'),
      'resultAmount' => Yii::_t('payments.payout-info.resultAmount'),
      'dateCreated' => Yii::_t('payments.payout-info.dateCreated'),
      'dateUpdated' => Yii::_t('payments.payout-info.dateUpdated'),
      'description' => Yii::_t('payments.payments.description'),
    ];
  }
}