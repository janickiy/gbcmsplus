<?php

namespace mcms\promo\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class LandingsFixture
 * @package mcms\promo\tests\fixtures
 */
class LandingsFixture extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\promo\models\Landing';

  /**
   * @inheritdoc
   */
  public $depends = ['promo.operators', 'promo.providers', 'promo.landing_categories'];
}