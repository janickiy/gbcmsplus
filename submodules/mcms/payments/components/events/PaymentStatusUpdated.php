<?php

namespace mcms\payments\components\events;

use mcms\common\event\Event;
use mcms\payments\models\UserPayment;
use Yii;

class PaymentStatusUpdated extends Event
{
  /** @var UserPayment|null */
  public $payment;

  /**
   * @inheritDoc
   */
  function __construct(UserPayment $payment = null)
  {
    $this->payment = $payment;
  }

  public function trigger()
  {
    // TRICKY При смене статуса на Отложена, событие не вызывается, так как партнер не должен знать о таком изменении. Ресу вызывается
    if (
      !$this->payment
      || !$this->owner
      || $this->payment->status != UserPayment::STATUS_ERROR
      || $this->owner->id == UserPayment::getResellerId()
    ) {
      parent::trigger();
    }
  }

  public function getOwner()
  {
    return $this->payment->user;
  }

  public static function getUrl($id = null)
  {
    return ['/partners/payments/balance/'];
  }

  public function getModelId()
  {
    return $this->payment->id;
  }

  function getEventName()
  {
    return Yii::_t('payments.events.payment-status-updated');
  }
}