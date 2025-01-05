<?php

namespace mcms\statistic\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class Subscriptions
 * @package mcms\statistic\tests\fixtures
 */
class Subscriptions extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $tableName = 'subscriptions';


  /**
   * @inheritdoc
   */
  public $depends = ['promo.sources'];
}