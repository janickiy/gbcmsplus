<?php

namespace mcms\pages\tests\fixtures;

use yii\test\ActiveFixture;
use mcms\common\traits\FixtureTrait;

/**
 * Class PageProp
 * @package mcms\pages\tests\fixtures
 */
class PageCategoryPropEntitiesFixture extends ActiveFixture
{
  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\pages\models\CategoryPropEntity';

  /**
   * @inheritdoc
   */
  public $depends = ['pages.page_category_props'];
}