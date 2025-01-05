<?php

namespace mcms\promo\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class ProvidersFixture
 * @package mcms\promo\tests\fixtures
 */
class ProvidersFixture extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\promo\models\Provider';

  /**
   * @inheritdoc
   */
  public $depends = ['users.users'];
}