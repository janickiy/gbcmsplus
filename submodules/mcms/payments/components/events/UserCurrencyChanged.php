<?php

namespace mcms\payments\components\events;


use mcms\common\event\Event;
use mcms\payments\models\UserPaymentSetting;
use Yii;

class UserCurrencyChanged extends Event
{
  /** @var  UserPaymentSetting */
  public $userPaymentSetting;

  /**
   * @inheritDoc
   */
  function __construct(UserPaymentSetting $userPaymentSetting = null)
  {
    $this->userPaymentSetting = $userPaymentSetting;
  }

  function getEventName()
  {
    return Yii::_t('payments.events.user-currency-changed');
  }
}