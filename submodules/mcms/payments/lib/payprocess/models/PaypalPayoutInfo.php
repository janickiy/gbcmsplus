<?php

namespace mcms\payments\lib\payprocess\models;

use Yii;

/**
 * Class PaypalPayoutInfo
 * @package mcms\payments\lib\payprocess\models
 */
class PaypalPayoutInfo extends BasePayoutInfo
{
  public $payout_item_id;
  public $status;
  public $payout_fee_currency;
  public $payout_fee_value;
  public $payout_amount_currency;
  public $payout_amount_value;
  public $note;
  public $receiver;
  public $recipient_type;
  public $sender_item_id;
  public $error_name;
  public $error_message;

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return array_merge([

    ], parent::attributeLabels());
  }
}