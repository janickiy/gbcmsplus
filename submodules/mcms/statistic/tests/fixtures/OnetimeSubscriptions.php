<?php

namespace mcms\statistic\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class OnetimeSubscriptions
 * @package mcms\statistic\tests\fixtures
 */
class OnetimeSubscriptions extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $tableName = 'onetime_subscriptions';


  /**
   * @inheritdoc
   */
  public $depends = ['users.users', 'promo.landing_operators'];
}