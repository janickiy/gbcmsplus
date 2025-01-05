<?php

namespace mcms\support\components\grid;

use yii\grid\DataColumn;

class SupportText extends DataColumn
{
  public function init()
  {
    parent::init();
    $this->value = function($model) {
      /** @var \mcms\support\models\Support $model */
      $textModel = $model->getText()->one();
      return $textModel ? $textModel->text : null;
    };
  }

}