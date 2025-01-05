<?php

namespace mcms\statistic\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;

class HitsDayGroup extends ActiveFixture
{
  use FixtureTrait;

  public $tableName = 'hits_day_group';

}
