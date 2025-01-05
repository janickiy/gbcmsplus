<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\Module;
use yii\helpers\ArrayHelper;

class FakeRevshareSettings extends ApiResult
{
  private $result = [];
  private $partnerId;

  function init($params = [])
  {
    $this->partnerId = ArrayHelper::getValue($params, 'partnerId');

    /** @var \mcms\promo\Module $module */
    $module = Module::getInstance();

    $this->result = [
      'on_subscriptions_after_subscriptions_count' => $module->settingsAddFakeSubscriptionsAfter($this->partnerId),
      'on_subscriptions_percent' => $module->settingsFakeSubscriptionPercent($this->partnerId),
      'off_subscriptions_time' => $module->settingsFakeOffSubscriptionDays(),
      'off_subscriptions_percent_before_time' => $module->settingsFakeOffSubscriptionsPercentBeforeDays(),
      'on_cpa_subscriptions_percent' => $module->settingsFakeCPASubscriptionPercent($this->partnerId),
      'is_globally_enabled_fake_to_users' => $module->settingsIsFakeGloballyEnabled() ? 1 : 0,
      'off_subscriptions_max_rejection_percent' => $module->settingsFakeOffSubscriptionMaxRejection(),
    ];
  }

  public function getResult()
  {
    return $this->result;
  }
}