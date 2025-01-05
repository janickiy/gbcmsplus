<?php

use yii\grid\GridView;
use mcms\common\helpers\Html;
use mcms\partners\components\helpers\DomainClassAttributeHelper;

/* @var mcms\common\web\View $this */
?>

<?= GridView::widget([
  'layout' => '{items}<div class="text-center">{pager}</div>',
  'dataProvider' => $sourcesDataProvider,
  'tableOptions' => [
    'class' => 'table table-striped table-domain'
  ],
  'rowOptions' => function($model, $key, $index, $grid) {
    return ['class' => $index % 2 == 1 ? 'even' : ''];
  },
  'columns' => [
    [
      'label' => Yii::_t('domains.domain_url'),
      'attribute' => 'url',
      'contentOptions' => [
        'data-label' => Yii::_t('domains.domain_url'),
      ]
    ],
    [
      'label' => Yii::_t('domains.domain_type'),
      'attribute' => 'type',
      'value' => function($model) {
        return $model->is_system ? Yii::_t('domains.domain_is_system') : $model->currentTypeName;
      },
      'contentOptions' => [
        'data-label' => Yii::_t('domains.domain_type'),
      ]
    ],
    [
      'label' => Yii::_t('domains.domain_status'),
      'value' => function($model) {
        return Html::tag('span', '', [
          'class' => 'icon-shield ' . DomainClassAttributeHelper::getDomainClass($model->isActive()),
          'data-original-title' => $model->currentStatusName,
          'data-placement' => 'left',
          'data-toggle' => 'tooltip',
        ]);
      },
      'format' => 'raw',
      'contentOptions' => [
        'data-label' => Yii::_t('domains.domain_status'),
      ]
    ],
  ]
]) ?>