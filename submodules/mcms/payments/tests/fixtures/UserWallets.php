<?php

namespace mcms\payments\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class UserPayments
 * @package mcms\payments\tests\fixtures
 */
class UserWallets extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\payments\models\UserWallet';


  /**
   * @inheritdoc
   */
  public $depends = ['users.users', 'payments.wallets'];
}