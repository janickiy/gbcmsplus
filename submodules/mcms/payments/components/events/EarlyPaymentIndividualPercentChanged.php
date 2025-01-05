<?php

namespace mcms\payments\components\events;


use mcms\common\event\Event;
use mcms\payments\models\UserPaymentSetting;
use Yii;

class EarlyPaymentIndividualPercentChanged extends Event
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
    return Yii::_t('payments.events.early-payment-individual-percent-changed');
  }
}