<?php

foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
  echo kartik\widgets\Growl::widget([
    'type' => $key,
    'icon' => 'glyphicon glyphicon-ok-sign',
    'body' => $message,
    'showSeparator' => false,
    'delay' => 0,
    'pluginOptions' => [
      'showProgressbar' => true,
      'placement' => [
        'from' => 'top',
        'align' => 'right',
      ]
    ]
  ]);
}