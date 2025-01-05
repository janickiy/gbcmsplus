<?php

namespace mcms\statistic\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class SubsDayGroup
 * @package mcms\statistic\tests\fixtures
 */
class SubsDayGroup extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $tableName = 'subscriptions_day_group';


  /**
   * @inheritdoc
   */
  public $depends = ['users.users', 'promo.landing_operators'];
}