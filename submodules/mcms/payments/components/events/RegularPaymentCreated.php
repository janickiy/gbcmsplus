<?php

namespace mcms\payments\components\events;

use mcms\common\event\Event;
use mcms\payments\models\UserPayment;
use Yii;

/**
 * Событие штатной выплаты
 * Class RegularPaymentCreated
 * @package mcms\payments\components\events
 */
class RegularPaymentCreated extends Event
{
  public $payment;
  /**
   * @inheritDoc
   */
  function __construct(UserPayment $payment = null)
  {
    $this->payment = $payment;
  }

  /**
   * Овнер
   * @inheritdoc
   */
  public function getOwner()
  {
    return $this->payment === null ? null : $this->payment->user;
  }

  /**
   * @inheritdoc
   */
  public function getModelId()
  {
    return $this->payment === null ? null : $this->payment->id;
  }

  /**
   * @inheritdoc
   */
  public function incrementBadgeCounter()
  {
    return $this->payment === null ? null : $this->payment->isAwaiting();
  }

  /**
   * @inheritdoc
   */
  public static function getUrl($id = null)
  {
    $currentUser = Yii::$app->user;

    if ($currentUser->can('PaymentsResellerCheckoutIndex')) {
      return ['/payments/reseller-checkout/index/'];
    }
    if ($currentUser->can('PartnersPaymentsBalance')) {
      return ['/partners/payments/balance/'];
    }

    return parent::getUrl($id);
  }

  /**
   * @inheritdoc
   */
  function getEventName()
  {
    return Yii::_t('payments.events.regular-payment-created');
  }


}