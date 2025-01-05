<?php

namespace mcms\statistic\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;

class HitParams extends ActiveFixture
{
  use FixtureTrait;

  public $tableName = 'hit_params';

}
