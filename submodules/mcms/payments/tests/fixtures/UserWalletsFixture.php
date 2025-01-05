<?php

namespace mcms\payments\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;

/**
 * Class WalletsFixture
 * @package mcms\payments\tests\fixtures
 */
class USerWalletsFixture extends ActiveFixture
{
  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\payments\models\UserWallet';
  public $depends = ['users.users', 'payments.wallets'];
}