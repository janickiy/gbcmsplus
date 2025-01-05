<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use mcms\promo\models\Domain;
use yii\widgets\Pjax;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\Select2;
use mcms\common\helpers\Html;

$id = 'domains';

/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \yii\db\ActiveRecord $searchModel
 */
?>

<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => \yii\bootstrap\Html::icon('plus') . ' ' . Yii::_t('promo.' . $id . '.create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['/promo/domains/create-modal'],
]); ?>
<?php $this->endBlock() ?>


<?= Html::beginTag('section', ['id' => 'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?php Pjax::begin(['id' => $id . 'PjaxGrid']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'rowOptions' => function ($model) {
    return ['class' => ArrayHelper::getValue($model::getStatusColors(), $model->status, '')];
  },
  'columns' => [
    [
      'attribute' => 'id',
      'contentOptions' => ['style' => 'width: 80px']
    ],
    [
      'attribute' => 'url',
      'format' => 'url',
      'contentOptions' => ['style' => 'width: 250px']
    ],
    [
      'attribute' => 'status',
      'filter' => $searchModel->getStatuses(),
      'value' => 'currentStatusName',
      'contentOptions' => ['style' => 'width: 120px']
    ],
    [
      'attribute' => 'type',
      'filter' => $searchModel->getTypes(),
      'value' => 'currentTypeName',
      'contentOptions' => ['style' => 'width: 120px']
    ],
    [
      'attribute' => 'is_system',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t('app.common.Yes'),
      'falseLabel' => Yii::_t('app.common.No'),
      'filterWidgetOptions' => [
        'pluginOptions' => [
          'allowClear' => true
        ],
        'options' => [
          'placeholder' => '',
        ],
      ],
    ],
    [
      'attribute' => 'user_id',
      'format' => 'raw',
      'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'user_id',
          'initValueUserId' => $searchModel->user_id,
          'options' => [
            'placeholder' => '',
          ],
        ]
      ),
      'enableSorting' => false,
      'value' => 'userLink',
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{view-modal} {update-modal}',
      'visibleButtons' => [
        'update-modal' => function (Domain $model) {
          return Yii::$app->user->identity->canViewUser($model->user_id);
        }
      ],
      'contentOptions' => ['class' => 'col-min-width-100'],
    ],

  ],
]); ?>
<?php Pjax::end(); ?>


<?php ContentViewPanel::end() ?>
<?= Html::endTag('section'); ?>