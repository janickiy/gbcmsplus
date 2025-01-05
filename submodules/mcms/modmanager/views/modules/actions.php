<?php

use mcms\common\helpers\Html;

$this->beginBlock('list_button');
echo Html::a(
  '<i class="glyphicon glyphicon-list"></i> ' . Yii::_t("controller.modules_list"),
  ['/modmanager/modules/index/'],
  ['class' => 'btn btn-primary']
);
$this->endBlock();