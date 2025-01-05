<?php

namespace mcms\promo\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class OperatorsFixture
 * @package mcms\promo\tests\fixtures
 */
class OperatorsFixture extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\promo\models\Operator';

  /**
   * @inheritdoc
   */
  public $depends = ['promo.countries', 'users.users'];
}