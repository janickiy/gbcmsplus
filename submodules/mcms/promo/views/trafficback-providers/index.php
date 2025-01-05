<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use yii\widgets\Pjax;
use mcms\promo\models\TrafficbackProvider;
use yii\bootstrap\Html;
use mcms\common\widget\Select2;

?>

<?php $this->beginBlock('actions'); ?>
  <?= $this->render('actions/create'); ?>
<?php $this->endBlock() ?>

<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
    'padding' => false,
]);
?>

<?php
$pjaxId = 'providers-trafficback-grid';
Pjax::begin([
  'options' => ['id' => $pjaxId],
]); ?>
<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'rowOptions' => function ($model) {
    return $model->status === $model::STATUS_INACTIVE ? ['class' => 'danger'] : [];
  },
  'columns' => [
    [
      'attribute' => 'id',
      'contentOptions' => ['style' => 'width: 80px']
    ],
    'name',
    'url:url',
    [
      'attribute' => 'category_id',
      'filter' => $searchModel->getCategoriesMap(),
      'value' => 'currentCategoryName'
    ],
    [
      'attribute' => 'status',
      'filter' => $searchModel->getStatuses(),
      'value' => 'currentStatusName'
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update} {disable} {enable}',
      'contentOptions' => ['class' => 'col-min-width-100'],
      'buttons' => [
        'update' => function ($url, $model) use ($pjaxId) {
          return Modal::widget([
            'options' => ['id' => $pjaxId . '-' . $model->id],
            'toggleButtonOptions' => [
              'tag' => 'a',
              'label' => Html::icon('pencil'),
              'class' => 'btn btn-xs btn-default',
              'data-pjax' => 0,
            ],
            'url' => $url,
          ]);
        },
      ],
    ],
  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>
