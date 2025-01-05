<?php

namespace mcms\payments\components\events;

use Yii;
use mcms\common\event\Event;
use mcms\payments\models\UserPayment;

/**
 * Событие досрочной выплаты
 * Class EarlyPaymentCreated
 * @package mcms\payments\components\events
 */
class EarlyPaymentCreated extends Event
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
   * @inheritdoc
   */
  public function incrementBadgeCounter()
  {
    return $this->payment && $this->payment->isAwaiting();
  }

  /**
   * @inheritdoc
   */
  public function getOwner()
  {
    return $this->payment->getUser()->one();
  }

  /**
   * @inheritdoc
   */
  public static function getUrl($id = null)
  {
    $currentUser = Yii::$app->user;

    if ($currentUser->can('PaymentsPaymentsView')) {
      return ['/payments/payments/view/', 'id' => $id];
    }

    if ($currentUser->can('PaymentsResellerViewPayment')) {
      return ['/payments/reseller/view-payment/', 'id' => $id];
    }

    return Event::getUrl($id);
  }

  /**
   * @inheritdoc
   */
  public function getModelId()
  {
    return $this->payment ? $this->payment->id : null;
  }

  /**
   * @inheritdoc
   */
  function getEventName()
  {
    return Yii::_t('payments.events.early-payment-created');
  }
}