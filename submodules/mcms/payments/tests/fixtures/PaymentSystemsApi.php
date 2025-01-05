<?php

namespace mcms\payments\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;

/**
 * Class PaymentSystemApi
 * @package mcms\payments\tests\fixtures
 */
class PaymentSystemsApi extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\payments\models\paysystems\PaySystemApi';
}