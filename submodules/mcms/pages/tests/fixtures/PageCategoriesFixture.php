<?php

namespace mcms\pages\tests\fixtures;

use yii\test\ActiveFixture;
use mcms\common\traits\FixtureTrait;

/**
 * Class PageCategoriesFixture
 * @package mcms\pages\tests\fixtures
 */
class PageCategoriesFixture extends ActiveFixture
{
  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\pages\models\Category';
}