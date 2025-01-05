<?php

namespace mcms\statistic\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class SoldSubscriptions
 * @package mcms\statistic\tests\fixtures
 */
class SoldSubscriptions extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $tableName = 'sold_subscriptions';


  /**
   * @inheritdoc
   */
  public $depends = ['users.users', 'promo.landing_operators'];
}