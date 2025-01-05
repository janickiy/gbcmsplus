<?php

namespace mcms\payments\components\events;


use mcms\common\event\Event;
use mcms\payments\models\UserPayment;
use Yii;

class PaymentUpdated extends Event
{

  public $model;

  /**
   * @inheritDoc
   */
  function __construct(UserPayment $model = null)
  {
    $this->model = $model;
    $this->owner = $model ? $model->user : null;
  }

  function getOwner()
  {
    return $this->owner;
  }

  function getEventName()
  {
    return Yii::_t('payments.events.payment-updated');
  }

  public function getModelId()
  {
    return $this->model->id;
  }

  public function trigger()
  {
    // Если овнер - рес, не отправляем уведомление (он получит другое, как овнер)
    if ($this->owner->id == UserPayment::getResellerId()) return;

    parent::trigger();
    Yii::$app->getModule('notifications')->api('setViewedByIdEvent', [
      'event' => [
        RegularPaymentCreated::class,
        EarlyPaymentCreated::class
      ],
      'modelId' => $this->getModelId()
    ])->getResult();

    Yii::$app->getModule('payments')->api('badgeCounters')->invalidateCache();
  }

  /**
   * @inheritdoc
   */
  public static function getUrl($id = null)
  {
    return ['/payments/payments/index/'];
  }
}