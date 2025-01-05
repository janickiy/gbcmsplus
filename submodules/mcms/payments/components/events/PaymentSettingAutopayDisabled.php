<?php

namespace mcms\payments\components\events;


use mcms\common\event\Event;
use mcms\payments\models\UserPaymentSetting;
use Yii;

class PaymentSettingAutopayDisabled extends Event
{
  public $model;

  /**
   * @inheritDoc
   */
  function __construct(UserPaymentSetting $model = null)
  {
    $this->model = $model;
  }

  function getEventName()
  {
    return Yii::_t('payments.events.payment-setting-autopay-disabled');
  }
}