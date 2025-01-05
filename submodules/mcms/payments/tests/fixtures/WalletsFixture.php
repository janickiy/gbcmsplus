<?php

namespace mcms\payments\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;

/**
 * Class WalletsFixture
 * @package mcms\payments\tests\fixtures
 */
class WalletsFixture extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\payments\models\wallet\Wallet';
}