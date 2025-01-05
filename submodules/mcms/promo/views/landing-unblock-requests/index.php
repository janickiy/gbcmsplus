<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\models\LandingUnblockRequest;
use yii\widgets\Pjax;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\Select2;
use kartik\date\DatePicker;
use mcms\common\helpers\Html;

$id = 'landing-unblock-requests';


/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \yii\db\ActiveRecord $searchModel
 */
?>

<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => \yii\bootstrap\Html::icon('plus') . ' ' . Yii::_t('promo.landing_unblock_requests.create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['/promo/landing-unblock-requests/create-modal'],
]); ?>
<?php $this->endBlock() ?>


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
    return ['class' => ArrayHelper::getValue($model::getStatusColors(), $model->status, '')];
  },
  'columns' => [
    [
      'attribute' => 'id',
      'contentOptions' => ['style' => 'width: 80px']
    ],
    [
      'attribute' => 'status',
      'filter' => $searchModel->getStatuses(),
      'value' => 'currentStatusName',
      'contentOptions' => ['style' => 'width: 120px']
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
          'pluginOptions' => [
              'allowClear' => true
          ]
        ]
      ),
      'enableSorting' => false,
      'value' => 'userLink',
    ],
    [
      'attribute' => 'landing_id',
      'format' => 'raw',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'landing_id',
        'initValueText' => ArrayHelper::getValue($select2InitValues, 'landing_id'),
        'options'=> [
            'placeholder' => '',
        ],
        'pluginOptions'=> [
          'allowClear' => true,
          'ajax' => [
            'url' => Url::to(['landings/select2']),
            'dataType' => 'json',
            'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }')
          ]
        ]
      ]),
      'enableSorting' => false,
      'value' => 'landingLink',
    ],
    [
      'attribute' => 'countries',
      'format' => 'raw',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'countries',
        'data' => $countries,
        'options' => [
          'placeholder' => '',
          'multiple' => true
        ],
        'pluginOptions' => [
          'allowClear' => true
        ]
      ]),
      'value' => function(LandingUnblockRequest $model) {
        return $model->landing->gridCountries;
      },
      'width' => '100px'
    ],
    [
      'attribute' => 'operators',
      'format' => 'raw',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'operators',
        'options' => [
          'prompt' => '',
          'multiple' => true
        ],
        'countriesId' => $searchModel->countries,
        'useSelect2' => true,
      ]),
      'value' => function(LandingUnblockRequest $model) {
        return $model->landing->gridOperators;
      },
      'width' => '250px'
    ],
    'description',
    [
      'attribute' => 'created_at',
      'format' =>  'datetime',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'createdFrom',
        'attribute2' => 'createdTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
          'orientation' => 'bottom',
          'autoclose' => true,
        ]
      ]),
      'contentOptions' => ['style' => 'width: 240px;']
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{view-modal} {update-modal}',
      'visible' => function ($model) {
        return Yii::$app->user->identity->canViewUser($model->user_id);
      },
      'contentOptions' => ['class' => 'col-min-width-100'],
    ],

  ],
]); ?>
<?php Pjax::end(); ?>


<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>