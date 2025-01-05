<?php
namespace mcms\statistic\tests\fixtures\statisticDataByHours;

use yii\test\ActiveFixture;
use mcms\common\traits\FixtureTrait;

class SubscriptionRebills extends ActiveFixture
{
  use FixtureTrait;

  public $tableName = 'subscription_rebills';
  public $depends = ['statistic.statistic_data_by_hours_subscriptions'];
}