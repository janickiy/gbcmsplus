<?php

namespace mcms\payments\components\events;

use mcms\common\event\Event;
use mcms\payments\models\UserPaymentSetting;
use Yii;
use yii\helpers\ArrayHelper;

class PartnerAutoPaymentsEnable extends Event
{
  public $userPaymentSettings;
  public $statisticApi;

  /**
   * @inheritDoc
   */
  function __construct(UserPaymentSetting $userPaymentSettings = null)
  {
    $this->userPaymentSettings = $userPaymentSettings;
    $this->statisticApi = $userPaymentSettings->user_id
      ? Yii::$app->getModule('statistic')->api('subscriptionsCount', [
        'userIds' => $userPaymentSettings->user_id
      ])
      : null
    ;
  }


  function getEventName()
  {
    return Yii::_t('payments.events.partner-auto-payments-enable');
  }

  public function getReplacements()
  {
    return [
      'userId' => $this->userPaymentSettings->user_id,
      'subscriptionCount' => $this->statisticApi
        ? ArrayHelper::getValue($this->statisticApi->getActiveSubscriptionsCount(), $this->userPaymentSettings->user_id)
        : null
    ];
  }

  public function getReplacementsHelp()
  {
    return [
      'userId' => Yii::_t('payments.events.userId'),
      'subscriptionCount' => Yii::_t('payments.user-payment-settings.replacement-attribute-subscription_count')
    ];
  }


}