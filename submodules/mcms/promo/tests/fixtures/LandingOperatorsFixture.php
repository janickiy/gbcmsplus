<?php

namespace mcms\promo\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class LandingOperatorsFixture
 * @package mcms\promo\tests\fixtures
 */
class LandingOperatorsFixture extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\promo\models\LandingOperator';

  /**
   * @inheritdoc
   */
  public $depends = ['promo.operators', 'promo.landings'];
}