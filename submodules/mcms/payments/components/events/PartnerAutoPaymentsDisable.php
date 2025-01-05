<?php

namespace mcms\payments\components\events;

use mcms\common\event\Event;
use mcms\payments\models\UserPaymentSetting;
use Yii;

class PartnerAutoPaymentsDisable extends PartnerAutoPaymentsEnable
{
  function getEventName()
  {
    return Yii::_t('payments.events.partner-auto-payments-disable');
  }
}