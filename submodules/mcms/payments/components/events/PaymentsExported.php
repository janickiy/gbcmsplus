<?php

namespace mcms\payments\components\events;

use mcms\common\event\Event;
use mcms\payments\models\export\UserPaymentExport;
use Yii;

class PaymentsExported extends Event
{
  /** @var UserPaymentExport */
  public $model;
  /**
   * @inheritDoc
   */
  function __construct(UserPaymentExport $model = null)
  {
    $this->model = $model;
  }

  function getEventName()
  {
    return Yii::_t('payments.events.payments-exported');
  }
}