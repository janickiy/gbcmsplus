<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\UserSelect2;
use mcms\support\models\Support;
use kartik\date\DatePicker;
use yii\widgets\Pjax;
use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\Select2;
use mcms\common\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var mcms\support\models\search\SupportSearch $searchModel
 */
$this->blocks['actions'] = $this->render('actions/create', ['model' => $searchModel]);
?>

<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?php Pjax::begin([
  'options' => [
    'id' => 'tickets-list-grid',
  ],
]); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'containerOptions'=>['style'=>'overflow: auto'],
  'rowOptions' => function ($model, $key, $index, $grid) {
    /** @var Support $model */
    $options = [];
    if ($model->isOpened() && $model->hasUnreadMessages()) {
      $options['class'] = AdminGridView::TYPE_WARNING;
    }

    return $options;
  },
  'columns' => [
    [
      'attribute' => 'id',
      'width' => '80px'
    ],
    [
      'attribute' => 'nameLink',
      'format' => 'raw',
    ],
    [
      'attribute' => 'support_category_id',
      'value' => 'supportCategory.name',
      'filter' => $searchModel->getCategoriesDropDown(),
      'contentOptions' => ['style' => 'width: 80px;']
    ],
    [
      'attribute' => 'created_by',
      'header' => Yii::_t('support.controller.ticket_createdBy') . ' <i class="glyphicon glyphicon-user"></i>',
      'vAlign'=> 'middle',
      'width' => '150px',
      'format' => 'raw',
      'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'created_by',
          'roles' => ['partner'],
          'initValueUserId' => $searchModel->created_by,
          'options' => [
            'placeholder' => '',
          ],
          'pluginOptions' => [
            'allowClear' => true,
          ]
        ]
      ),
      'value' => 'createdByLink',
      'enableSorting' => false,
    ],
    [
      'attribute' => 'delegated_to',
      'header' => Yii::_t('support.controller.ticket_delegatedTo') . ' <i class="glyphicon glyphicon-user"></i>',
      'vAlign'=> 'middle',
      'width' => '150px',
      'format' => 'raw',
      'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'delegated_to',
          'roles' => $searchModel->getDelegatedToRoles(),
          'initValueUserId' => $searchModel->delegated_to,
          'options' => [
            'placeholder' => '',
          ],
          'pluginOptions' => [
            'allowClear' => true,
          ]
        ]
      ),
      'value' => 'delegatedToLink',
      'enableSorting' => false,
    ],
    [
      'attribute' => 'is_opened',
      'value' => 'openedName',
      'filter' => $searchModel->getIsOpened(),
      'contentOptions' => ['style' => 'width: 80px;']
    ],
    [
      'attribute' => 'has_unread_messages',
      'value' => 'hasUnreadName',
      'filter' => $searchModel->getHasUnread(),
      'contentOptions' => ['style' => 'width: 80px;']
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
      'width' => '200px',
    ],
  ]
]); ?>

<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
