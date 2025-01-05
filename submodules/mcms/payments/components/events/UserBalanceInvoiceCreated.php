<?php

namespace mcms\payments\components\events;


use mcms\common\event\Event;
use mcms\payments\models\UserBalanceInvoice;
use Yii;

class UserBalanceInvoiceCreated extends Event
{

  /** @var  UserBalanceInvoice */
  public $model;

  /**
   * @inheritDoc
   */
  function __construct(UserBalanceInvoice $userBalanceInvoice = null)
  {
    $this->model = $userBalanceInvoice;
  }

  function getEventName()
  {
    return Yii::_t('payments.events.user-balance-invoice-created');
  }

  public function trigger()
  {
    // компенсация
    if ($this->model->type == UserBalanceInvoice::TYPE_COMPENSATION) {
      (new UserBalanceInvoiceCompensation($this->model))->trigger();
      return ;
    }
    // штраф
    if ($this->model->type == UserBalanceInvoice::TYPE_PENALTY) {
      (new UserBalanceInvoiceMulct($this->model))->trigger();
      return ;
    }
  }

  function getOwner()
  {
    return $this->owner;
  }
}