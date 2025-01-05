<?php
use kartik\date\DatePicker;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use mcms\notifications\models\search\NotificationsDeliverySearch;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;
use mcms\notifications\models\NotificationsDelivery;

/** @var array $modules */
/** @var NotificationsDeliverySearch $searchModel */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var array $notificationTypes [id => label] */


$this->blocks['actions'] = Modal::widget([
  'toggleButtonOptions' => [
    'id' => 'show-shortcut',
    'tag' => 'a',
    'label' => Html::icon('plus') . ' ' . Yii::_t('main.create_notifications'),
    'class' => 'btn btn-default',
    'data-pjax' => 0,
  ],
  'size' => Modal::SIZE_LG,
  'url' => Url::to(['/notifications/notifications/create']),
]);

ContentViewPanel::begin([
  'padding' => false,
]); ?>
<?php Pjax::begin(['id' => 'notificationsDeliveryPjaxGrid']); ?>
<?= AdminGridView::widget([
  'id' => 'notificationsDeliveryGrid',
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    [
      'attribute' => 'header',
      'format' => 'raw',
      'value' => function ($model) {
        return Modal::widget([
          'toggleButtonOptions' => array_merge([
            'tag' => 'a',
            'label' => Yii::$app->formatter->asRaw((string)$model->header ?: null),
            'title' => Yii::t('yii', 'View'),
            'data-pjax' => 0,
          ]),
          'url' => Url::to(['/notifications/delivery/view/', 'id' => $model->id]),
        ]);
      }
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
      'value' => function ($model) {
        return $model->is_manual ? $model->userLink : Yii::_t('notifications.main.auto_notification');
      },
      'contentOptions' => ['style' => 'width: 200px;'],
      'visible' => \Yii::$app->user->can('NotificationsDeliveryNotOwn'),
    ],
    [
      'attribute' => 'from_module_id',
      'filter' => $modules,
      'value' => function ($model) {
        return Yii::_t($model->module->name);
      },
      'contentOptions' => ['style' => 'width: 150px;']
    ],
    [
      'attribute' => 'event',
      'filter' => NotificationsDeliverySearch::getEventFilterVariants(),
      'value' => function ($model) {
        $class = $model->event;
        /** @var \mcms\common\event\Event $event */
        $event = class_exists($class) ? new $class : null;
        return $event ? $event->getEventName() : null;
      },
      'contentOptions' => ['style' => 'width: 150px;']
    ],
    [
      'attribute' => 'notification_type',
      'filter' => $notificationTypes,
      'value' => function (NotificationsDelivery $model) use ($notificationTypes) {
        return ArrayHelper::getValue($notificationTypes, $model->notification_type, $model->notification_type);
      },
    ],
    [
      'attribute' => 'is_important',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t('app.common.Yes'),
      'falseLabel' => Yii::_t('app.common.No'),
    ],
    [
      'attribute' => 'created_at',
      'format' => 'datetime',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'dateBegin',
        'attribute2' => 'dateEnd',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'pluginOptions' => ['format' => 'yyyy-mm-dd', 'orientation' => 'bottom', 'autoclose' => true]
      ]),
      'contentOptions' => ['style' => 'width: 150px;']
    ],
  ],
]); ?>

<?php Pjax::end(); ?>
<?php ContentViewPanel::end();
