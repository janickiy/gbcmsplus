<?php

namespace mcms\promo\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class DomainsFixture
 * @package mcms\promo\tests\fixtures
 */
class DomainsFixture extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\promo\models\Domain';

  /**
   * @inheritdoc
   */
  public $depends = ['users.users'];
}