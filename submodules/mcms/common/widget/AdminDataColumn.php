<?php

namespace mcms\common\widget;

use kartik\grid\DataColumn;
use mcms\common\helpers\Html;

class AdminDataColumn extends DataColumn
{
  public function init()
  {
    parent::init();
    Html::addCssClass($this->headerOptions, 'sortingCell');
  }
}