<?php

namespace mcms\payments\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class UserBalancesGroupedByDay
 * @package mcms\payments\tests\fixtures
 */
class UserBalancesGroupedByDay extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\payments\models\UserBalancesGroupedByDay';


  /**
   * @inheritdoc
   */
  public $depends = ['users.users'];
}