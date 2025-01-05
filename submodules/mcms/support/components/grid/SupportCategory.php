<?php

namespace mcms\support\components\grid;

use yii\grid\DataColumn;

class SupportCategory extends DataColumn
{
  public function init()
  {
    $this->value = function($model) {
      /** @var \mcms\support\models\Support $model */
      return $model->getSupportCategory()->one()->name;
    };
    parent::init();
  }

}