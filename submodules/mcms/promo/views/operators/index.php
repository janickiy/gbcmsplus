<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Html;
use mcms\common\widget\Select2;

$id = 'operators';

/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \yii\db\ActiveRecord $searchModel
 * @var bool $canChangeOperatorShowServiceUrl
 */
$this->blocks['actions'] =
  Modal::widget([
    'toggleButtonOptions' => [
      'tag' => 'a',
      'id' => 'show-shortcut',
      'class' => 'btn btn-success',
      'label' => Html::icon('plus') . ' ' . Yii::_t('promo.operators.create')
    ],
    'url' => ['/promo/operators/create'],
  ]);
?>

<?= Html::beginTag('section',['id'=>'widget-grid']);
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
    return $model->status === $model::STATUS_INACTIVE ? ['class' => 'danger'] : [];
  },
  'columns' => [
    [
      'attribute' => 'id',
      'contentOptions' => ['style' => 'width: 80px']
    ],
    'name',
    [
      'attribute' => 'status',
      'filter' => $searchModel->getStatuses(),
      'value' => 'currentStatusName',
      'visible' => Yii::$app->user->can('PromoOperatorsDetailList'),
    ],
    [
      'attribute' => 'is_3g',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t('app.common.on'),
      'falseLabel' => Yii::_t('app.common.off'),
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
      'attribute' => 'is_geo_default',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t('app.common.on'),
      'falseLabel' => Yii::_t('app.common.off'),
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
      'attribute' => 'show_service_url',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t('app.common.on'),
      'falseLabel' => Yii::_t('app.common.off'),
      'visible' => $canChangeOperatorShowServiceUrl,
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
        'attribute' => 'country_id',
        'label' => Yii::_t('promo.operators.attribute-country_id'),
        'filter' => Select2::widget([
            'model' => $searchModel,
            'attribute' => 'country_id',
            'data' => $countries,
            'options' => [
                'placeholder' => '',
            ],
            'pluginOptions' => [
                'allowClear' => true
            ]
          ]),
        'format' => 'raw',
        'value' => 'countryLink'
    ],
    [
      'header' => Yii::_t('promo.landings.main'),
      'format' => 'raw',
      'value' => 'landingsLink'
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{view-modal} {update} {disable} {enable}',
      'contentOptions' => ['class' => 'col-min-width-100'],
      'buttons' => [
        'update' => function($url, $model, $key) {
          return Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'a',
              'id' => 'updateOperator' . $model->id,
              'class' => 'btn btn-xs btn-default',
              'label' => Html::icon('pencil')
            ],
            'url' => ['operators/update', 'id' => $model->id],
          ]);
        },
      ]
    ],
  ],
]); ?>
<?php Pjax::end(); ?>


<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>