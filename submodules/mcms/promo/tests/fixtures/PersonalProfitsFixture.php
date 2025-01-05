<?php

namespace mcms\promo\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class PersonalProfitsFixture
 * @package mcms\promo\tests\fixtures
 */
class PersonalProfitsFixture extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\promo\models\PersonalProfit';

  /**
   * @inheritdoc
   */
  public $depends = ['promo.operators', 'promo.landings', 'users.users'];
}