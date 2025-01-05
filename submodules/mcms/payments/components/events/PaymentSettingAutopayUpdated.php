<?php

namespace mcms\payments\components\events;


use mcms\common\event\Event;
use Yii;

class PaymentSettingAutopayUpdated extends Event
{
  public $found;
  public $changed;

  /**
   * @inheritDoc
   */
  function __construct($found = 0, $changed = 0)
  {
    $this->found = $found;
    $this->changed = $changed;
  }

  function getEventName()
  {
    return Yii::_t('payments.events.payment-setting-autopay-updated');
  }
}