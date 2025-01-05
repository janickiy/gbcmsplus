<?php

namespace mcms\partners\components\widgets;

use Yii;
use yii\helpers\Html;

/**
 * Class StatisticPagerWidget
 * @package mcms\partners\components\widgets
 */
class StatisticPagerWidget extends \yii\widgets\LinkPager
{
  /**
   * @inheritdoc
   */
  protected function renderPageButtons()
  {
    $pageCount = $this->pagination->getPageCount();
    if ($pageCount < 2 && $this->hideOnSinglePage) return '';

    return Html::tag('div',
      Html::tag('div',
        parent::renderPageButtons(),
        ['class' => 'dataTables_paginate paging_simple_numbers', 'id' => 'example_paginate']
      ),
      ['class' => 'bottom']
    );
  }

}