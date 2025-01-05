<?php

namespace mcms\payments\components\events;


use mcms\common\event\Event;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use Yii;

class PaymentCreated extends Event
{
  public $model;
  /**
   * @inheritDoc
   */
  function __construct(UserPayment $model = null)
  {
    $this->model = $model;
  }

  function getOwner()
  {
    return $this->model->user;
  }

  public function trigger()
  {
    $model = $this->model;
    Yii::$app->getModule('payments')->api('badgeCounters')->invalidateCache();
    //заказана досрочная выплата
    if ($model->isManual()) {
      (new EarlyPaymentCreated($model))->trigger();
      return ;
    }
    //Сформирована штатная выплата или добавлена досрочная выплата из админки
    if ($model->isAdminManual() || $model->isGenerated()) {
      (new RegularPaymentCreated($model))->trigger();
      return ;
    }
  }

  function getEventName()
  {
    return Yii::_t('payments.events.payment-created');
  }
}