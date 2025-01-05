<?php
use mcms\notifications\components\assets\CopyReplacementValueAsset;
CopyReplacementValueAsset::register($this);
?>

<?= \yii\grid\GridView::widget([
  'options' => empty($options) ? [] : $options,
  'dataProvider' => $replacementsDataProvider,
  'tableOptions' => [
    'class' => 'table table-striped table-bordered table table-break-word'
  ],
  'columns' => [
    [
      'format' => 'raw',
      'value' => function ($model) {
        return \yii\bootstrap\Html::a(\kartik\helpers\Html::icon('copy'), "#", [
          'class' => 'btn btn-xs btn-default copy-replacements-value',
          'data-text' => $model['key'],
        ]);
      },
    ],
    [
      'attribute' => 'key',
      'label' => Yii::_t('notifications.replacements_label_key'),
      'format' => 'raw',
    ],
    [
      'attribute' => 'help',
      'label' => Yii::_t('notifications.replacements_label_help')
    ]
  ]
]) ?>