<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use kartik\date\DatePicker;
use mcms\common\widget\modal\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use mcms\promo\models\AdsNetwork;
use mcms\common\helpers\Link;
use yii\helpers\Html;
?>

<?php $this->beginBlock('actions'); ?>

<?php $link = Link::get('create') ?>

<?php if ($link) : ?>
  <?= Modal::widget([
    'toggleButtonOptions' => [
      'tag' => 'a',
      'label' => \yii\bootstrap\Html::icon('plus') . ' ' . AdsNetwork::translate('create'),
      'class' => 'btn btn-success btn-xs',
      'data-pjax' => 0,
    ],
    'url' => Url::to(['create']),
  ]) ?>
<?php else : ?>
  <?= $link ?>
<?php endif ?>

<?php $this->endBlock() ?>


<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?php
$pjaxId = 'adsNetworksGrid';
Pjax::begin(['id' => '' . $pjaxId]); ?>

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
    'label1',
    'description1',
    'label2',
    'description2',
    [
      'attribute' => 'status',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => AdsNetwork::translate("status-active"),
      'falseLabel' => AdsNetwork::translate("status-inactive"),
    ],
    [
        'attribute' => 'created_at',
        'format' => 'datetime',
        'filter' => DatePicker::widget([
          'model' => $searchModel,
          'attribute' => 'createdFrom',
          'attribute2' => 'createdTo',
          'type' => DatePicker::TYPE_RANGE,
          'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
          'pluginOptions' => ['format' => 'yyyy-mm-dd', 'orientation' => 'bottom']
        ]),
      ],
      [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{view-modal} {update} {disable} {enable}',
        'contentOptions' => ['class' => 'col-min-width-150'],
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

  ],
]); ?>
<?php Pjax::end(); ?>


<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>