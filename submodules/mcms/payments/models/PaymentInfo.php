<?php

namespace mcms\payments\models;


use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Wallet;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class PaymentInfo extends Model
{
  /** @var  AbstractWallet */
  public $recipient;
  /** @var  AbstractWallet */
  public $sender;
  public $amount;

  /**
   * @inheritDoc
   */
  public function __construct($walletType= null, $currency = null, $config = [])
  {
    if (!$walletType) {
      return;
    }
    if ($recipient = ArrayHelper::getValue($config, 'recipient')) {
      $config['recipient'] = Wallet::getObject($walletType, $recipient);
    }
    if ($sender = ArrayHelper::getValue($config, 'sender')) {
      $config['sender'] = Wallet::getObject($walletType, $sender);
    }

    parent::__construct($config);
  }


  /**
   * @inheritDoc
   */
  function __toString()
  {
    if ($attributes = array_filter($this->getAttributes())) {
      return Json::encode($attributes);
    }
    return '';
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'amount' => Yii::_t('payments.attribute-actual-amount')
    ];
  }

}