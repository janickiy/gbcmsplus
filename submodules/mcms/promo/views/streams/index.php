<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\UserSelect2;
use yii\widgets\Pjax;
use mcms\common\helpers\ArrayHelper;
use kartik\date\DatePicker;
use yii\bootstrap\Html;

$id = 'streams';


/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \yii\db\ActiveRecord $searchModel
 */
$userModule = Yii::$app->getModule('users');
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
    return ['class' => ArrayHelper::getValue($model::getStatusColors(), $model->status, '')];
  },
  'columns' => [
    [
        'attribute' => 'id',
        'contentOptions' => ['style' => 'width: 80px;']
      ],
      [
        'attribute' => 'name',
        'contentOptions' => ['style' => 'width: 120px;']
      ],
    [
      'attribute' => 'user_id',
      'format' => 'raw',
      'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'user_id',
          'roles' => [$userModule::PARTNER_ROLE],
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
      'attribute' => 'status',
      'filter' => $searchModel->getStatuses(),
      'value' => 'currentStatusName',
      'contentOptions' => ['style' => 'width: 120px;']
    ],
    [
      'header' => Yii::_t('promo.arbitrary_sources.main'),
      'format' => 'raw',
      'value' => 'sourcesLink'
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{view-modal} {update-modal} {disable} {enable}',
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