<?php

namespace mcms\support\components\grid;

use yii\grid\DataColumn;

class CategoryNameColumn extends DataColumn
{
  public function init()
  {
    parent::init();
    $this->value = function($model) {
      return $model->getName();
    };
  }
}