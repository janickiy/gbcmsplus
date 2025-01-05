<?php

use kartik\tabs\TabsX;
use mcms\common\helpers\Html;

$items = [];

if (isset($tabForms['main'])) {
  $items = [
    [
      'label' => Yii::_t('app.common.multilang_form_main'),
      'content' => $tabForms['main'],
    ]
  ];
}

foreach ($languages as $lang) {
  $items[] = [
    'label' => strtoupper($lang),
    'content' => $tabForms[$lang],
  ];
}

echo TabsX::widget([
  'items' => $items,
  'position' => TabsX::POS_ABOVE,
  'encodeLabels' => false,
  'options' => ['id' => Html::getUniqueId()],
]);

