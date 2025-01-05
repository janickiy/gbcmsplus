<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use mcms\promo\models\Provider;
use yii\widgets\Pjax;
use yii\bootstrap\Html;

/** @var bool $canViewAllFields */
?>

<?php $this->beginBlock('actions'); ?>
  <?= $this->render('actions/create'); ?>
  <?= $this->render('actions/create_external'); ?>
<?php $this->endBlock() ?>


<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
    'padding' => false,
]);
?>

<?php
$pjaxId = 'providers-info';
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
    'code',
    'url:url',
    [
      'attribute' => 'status',
      'filter' => $searchModel->getStatuses(),
      'value' => 'currentStatusName'
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{view-modal} {update} {disable} {enable}',
      'contentOptions' => ['class' => 'col-min-width-100'],
      'buttons' => [
        'view-modal' => function($url) {
          return Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'a',
              'label' => \yii\bootstrap\Html::icon('eye-open'),
              'title' => Yii::t('yii', 'View'),
              'class' => 'btn btn-xs btn-default',
              'data-pjax' => 0,
            ],
            'url' => $url,
          ]);
        },
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
        'redirect' => function ($url, $model, $key) {
          return \mcms\common\helpers\Link::get('/promo/providers/redirect', ['id' => $model->id], ['class' => 'btn btn-xs btn-info', 'title' => Yii::_t('promo.providers.redirect')], '<i class="glyphicon glyphicon-random"></i>');
        },
      ],
      'visibleButtons' => [
        'update' => function (Provider $model) use ($canViewAllFields) {
          return $canViewAllFields || !$model->is_rgk;
        }
      ],
    ],
  ],
]); ?>
<?php Pjax::end(); ?>


<?php ContentViewPanel::end() ?>
<?php if (Yii::$app->user->can('PromoProvidersTest')) { ?>
  <div class="pull-right">
    <?= Html::a(Html::icon('glyphicon glyphicon-new-window') . ' Provider tester', ['providers/test'], ['target' => '_blank']);?>
  </div>
<?php } ?>
<?= Html::endTag('section');
