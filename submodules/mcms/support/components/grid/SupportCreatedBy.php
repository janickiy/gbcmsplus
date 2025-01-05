<?php

namespace mcms\support\components\grid;

use yii\grid\DataColumn;

class SupportCreatedBy extends DataColumn
{
  public function init()
  {
    parent::init();
    $this->value = function($model) {
      /** @var \mcms\support\models\Support $model */
      return $model->getCreatedBy()->one()->username;
    };
  }

}