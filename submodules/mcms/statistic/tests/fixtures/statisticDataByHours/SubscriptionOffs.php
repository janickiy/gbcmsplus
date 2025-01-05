<?php
namespace mcms\statistic\tests\fixtures\statisticDataByHours;

use yii\test\ActiveFixture;
use mcms\common\traits\FixtureTrait;

class SubscriptionOffs extends ActiveFixture
{
  use FixtureTrait;

  public $tableName = 'subscription_offs';
  public $depends = ['statistic.statistic_data_by_hours_subscriptions'];
}