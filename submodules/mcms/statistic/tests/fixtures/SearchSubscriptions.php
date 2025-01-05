<?php

namespace mcms\statistic\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class SearchSubscriptions
 * @package mcms\statistic\tests\fixtures
 */
class SearchSubscriptions extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $tableName = 'search_subscriptions';
}