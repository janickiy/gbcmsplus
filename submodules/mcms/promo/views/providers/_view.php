<?php

use mcms\promo\models\AbstractProviderSettings;
use mcms\promo\models\Provider;
use yii\widgets\DetailView;

/** @var Provider $model Провайдер */
/** @var AbstractProviderSettings|null $settings Настройки провайдера */
/** @var bool $canViewAllFields */
?>

<?= DetailView::widget([
  'model' => $model,
  'options' => [
    'class' => 'table table-bordered table-break-word table-striped'
  ],
  'attributes' => [
    'id',
    'name',
    'code',
    'url:url',
    [
      'attribute' => 'status',
      'value' => $model->currentStatusName
    ],
    [
      'attribute' => 'handler_class_name',
      'value' => $model->getHandler()['name'],
    ],
    [
      'attribute' => 'settings',
      'format' => 'raw',
      'value' => $settings ? DetailView::widget(['model' => $settings]) : null,
      'contentOptions' => $settings ? ['style' => 'padding:0;border-top:0'] : [],
    ],
    [
      'attribute' => 'created_at',
      'format' => 'datetime',
      'visible' => $canViewAllFields,
    ],
    [
      'attribute' => 'updated_at',
      'format' => 'datetime',
      'visible' => $canViewAllFields,
    ],
    [
      'attribute' => 'created_by',
      'value' => $model->createdBy->username,
      'visible' => $canViewAllFields,
    ],
    [
      'attribute' => 'secret_key',
      'visible' => !$model->is_rgk,
    ]

  ]
]); ?>
