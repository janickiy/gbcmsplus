<?php

namespace mcms\promo\components\grid;

use yii\helpers\Html;
use yii\grid\DataColumn;

class ArbitrarySource extends DataColumn
{
  public function init()
  {
    parent::init();
    $this->value = function($model) {
      /** @var \mcms\promo\models\Source $model */
      return Html::a($model->getLink(), $model->getLink());
    };
  }

}