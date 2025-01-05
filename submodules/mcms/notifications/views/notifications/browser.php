<?php

use kartik\date\DatePicker;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;


ContentViewPanel::begin([
  'padding' => false,
]); ?>
<?php Pjax::begin(['id' => 'browserNotificationsPjaxGrid']); ?>
<?= AdminGridView::widget([
  'id' => 'browserGrid',
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
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
      'value' => 'userLink',
      'contentOptions' => ['style' => 'width: 200px;'],
    ],
    [
      'attribute' => 'from_user_id',
      'format' => 'raw',
      'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'from_user_id',
          'initValueUserId' => $searchModel->from_user_id,
          'options' => [
            'placeholder' => '',
          ],
        ]
      ),
      'value' => function ($model) {
        return $model->from_user_id ? $model->fromUserLink : Yii::_t('notifications.main.auto_notification');
      },
      'visible' => \Yii::$app->user->can('NotificationsNotificationsBrowserNotOwn'),
    ],
    [
      'attribute' => 'from_module_id',
      'filter' => $modules,
      'value' => function ($model) {
        return Yii::_t($model->module->name);
      }
    ],
    'header',
    [
      'attribute' => 'is_viewed',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t('app.common.Yes'),
      'falseLabel' => Yii::_t('app.common.No'),
    ],
    [
      'attribute' => 'is_important',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t('app.common.Yes'),
      'falseLabel' => Yii::_t('app.common.No'),
    ],
    [
      'attribute' => 'is_news',
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
      'contentOptions' => ['style' => 'width: 240px;']
    ],
    [
      'attribute' => 'updated_at',
      'format' => 'datetime',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'updatedFrom',
        'attribute2' => 'updatedTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'pluginOptions' => ['format' => 'yyyy-mm-dd', 'orientation' => 'bottom', 'autoclose' => true]
      ]),
      'contentOptions' => ['style' => 'width: 240px;']
    ],
    [
      'format' => 'raw',
      'value' => function ($model) {
        return ($model->notifications_delivery_id ?
            Html::a(Html::icon('list'),
              [
                '/notifications/delivery/index/',
                'NotificationsDeliverySearch[id]' => $model->notifications_delivery_id,
                'NotificationsDeliverySearch[notification_type]' => $model->delivery->notification_type,
                'NotificationsDeliverySearch[is_manual]' => $model->delivery->is_manual,
              ],
              ['class' => 'btn btn-xs btn-default', 'data-pjax' => 0, 'title' => Yii::_t('notifications.main.delivery')]) : '') .
          Modal::widget([
            'toggleButtonOptions' => array_merge([
              'tag' => 'a',
              'label' => Html::icon('eye-open'),
              'title' => Yii::t('yii', 'View'),
              'class' => 'btn btn-xs btn-default',
              'data-pjax' => 0,
            ]),
            'url' => Url::to(['/notifications/notifications/browser-view-modal/', 'id' => $model->id]),
          ]);
      },
      'contentOptions' => ['style' => 'min-width: 50px;']
    ],
  ],
]); ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end() ?>