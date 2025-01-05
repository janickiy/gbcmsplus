<?php

namespace mcms\payments\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class UserBalanceInvoices
 * @package mcms\payments\tests\fixtures
 */
class UserBalanceInvoices extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\payments\models\UserBalanceInvoice';


  /**
   * @inheritdoc
   */
  public $depends = ['users.users'];
}