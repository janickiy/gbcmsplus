<?php

namespace mcms\payments\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class UserPayments
 * @package mcms\payments\tests\fixtures
 */
class UserPayments extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\payments\models\UserPayment';


  /**
   * @inheritdoc
   */
  public $depends = ['users.users', 'payments.user_payment_settings', 'payments.user_balance_invoices'];
}