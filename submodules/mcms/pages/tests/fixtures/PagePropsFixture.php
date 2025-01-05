<?php

namespace mcms\pages\tests\fixtures;

use yii\test\ActiveFixture;
use mcms\common\traits\FixtureTrait;

/**
 * Class PageProp
 * @package mcms\pages\tests\fixtures
 */
class PagePropsFixture extends ActiveFixture
{
  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\pages\models\PageProp';

  /**
   * @inheritdoc
   */
  public $depends = ['pages.pages', 'pages.page_category_props', 'pages.page_category_prop_entities'];
}