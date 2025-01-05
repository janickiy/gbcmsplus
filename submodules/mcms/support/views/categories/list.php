<?php

use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use mcms\support\models\Support;
use kartik\date\DatePicker;
use yii\widgets\Pjax;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 */
$this->blocks['actions'] = $this->render('actions/create');
?>

<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?php
$pjaxId = 'support-categories-grid';
Pjax::begin([
    'options' => [
      'id' => $pjaxId,
    ],
  ]); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'export' => false,
  'containerOptions'=>['style'=>'overflow: auto'],
  'rowOptions' => function ($model) {
    return ['class' => $model->is_disabled ? 'danger' : ''];
  },
  'columns' => [
    'id',
    'name',
    'rolesList',
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update} {disable} {enable}',
      'buttons' => [
        'update' => function ($url, $model) use ($pjaxId) {
          return Modal::widget([
            'options' => ['id' => $pjaxId . '-' . $model->id],
            'toggleButtonOptions' => [
              'tag' => 'a',
              'label' => \yii\bootstrap\Html::icon('pencil'),
              'class' => 'btn btn-xs btn-default',
              'data-pjax' => 0,
            ],
            'url' => $url,
          ]);
        },
      ],
    ],
  ]
]); ?>

<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>