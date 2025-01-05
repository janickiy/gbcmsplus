<?php
namespace mcms\statistic\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;

class Hits extends ActiveFixture
{
  use FixtureTrait;

  public $tableName = 'hits';
  public $depends = ['promo.landings', 'promo.countries', 'promo.sources', 'promo.operators'];
}