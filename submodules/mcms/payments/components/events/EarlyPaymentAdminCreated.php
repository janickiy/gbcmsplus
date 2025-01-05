<?php

namespace mcms\payments\components\events;

use mcms\common\event\Event;
use mcms\payments\models\UserPayment;
use Yii;
use yii\helpers\Url;

class EarlyPaymentAdminCreated extends Event
{
  public $payment;
  /**
   * @inheritDoc
   */
  function __construct(UserPayment $payment = null)
  {
    $this->payment = $payment;
  }

  public function getOwner()
  {
    return $this->payment->user;
  }

  public function getModelId()
  {
    return $this->payment->id;
  }

  public static function getUrl($id = null)
  {
    return ['/partners/payments/balance/'];
  }

  function getEventName()
  {
    return Yii::_t('payments.events.early-payment-admin-created');
  }


}