<?php

namespace mcms\support\components\grid;

use Yii;
use yii\grid\DataColumn;

class SupportIsOpened extends DataColumn
{
  public function init()
  {
    parent::init();
    $this->value = function($model) {
      /** @var \mcms\support\models\Support $model */
      return Yii::_t("support.controller.ticket_" . ($model->isOpened() ? "opened" : "closed"));
    };
  }

}