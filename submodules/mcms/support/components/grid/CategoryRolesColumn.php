<?php

namespace mcms\support\components\grid;

use yii\grid\DataColumn;

class CategoryRolesColumn extends DataColumn
{
  public function init()
  {
    parent::init();
    $this->value = function($model) {
      return $model->getRolesList();
    };
  }
}