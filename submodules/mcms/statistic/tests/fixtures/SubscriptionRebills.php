<?php

namespace mcms\statistic\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class SubscriptionRebills
 * @package mcms\statistic\tests\fixtures
 */
class SubscriptionRebills extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $tableName = 'subscription_rebills';


  /**
   * @inheritdoc
   */
  public $depends = ['promo.sources'];
}