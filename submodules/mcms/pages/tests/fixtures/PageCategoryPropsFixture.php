<?php

namespace mcms\pages\tests\fixtures;

use yii\test\ActiveFixture;
use mcms\common\traits\FixtureTrait;

/**
 * Class CategoryProp
 * @package mcms\pages\tests\fixtures
 */
class PageCategoryPropsFixture extends ActiveFixture
{
  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\pages\models\CategoryProp';

  /**
   * @inheritdoc
   */
  public $depends = ['pages.page_categories'];
}