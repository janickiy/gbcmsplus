<?php
namespace mcms\statistic\tests\fixtures\statisticDataByHours;

use yii\test\ActiveFixture;
use mcms\common\traits\FixtureTrait;

class Subscriptions extends ActiveFixture
{
  use FixtureTrait;

  public $tableName = 'subscriptions';
  public $depends = ['statistic.statistic_data_by_hours_hits'];
}