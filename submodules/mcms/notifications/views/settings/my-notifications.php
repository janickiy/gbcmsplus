<?php


use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\notifications\components\assets\PushAsset;
use mcms\notifications\models\Notification;
use mcms\notifications\models\search\MyNotificationSearch;
use yii\helpers\Url;
use yii\widgets\Pjax;
use mcms\common\widget\AjaxButtons;
use mcms\notifications\components\telegram\Api;

/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var MyNotificationSearch $searchModel
 * @var $telegramId integer|null
 * @var $isTelegramConfigured bool
 * @var $isPushEnabled bool
 */
AjaxButtons::widget();
PushAsset::register($this);
$userName = Yii::$app->user->identity->username;
$js = <<<JS
window.firebaseSubscribe = new firebaseSubscribe('sentFirebaseMessagingToken_$userName');
JS;
$this->registerJs($js);
?>

<?php Pjax::begin() ?>
<div class="panel panel-default">
  <div class="panel-heading" role="tab" id="notificationsHeadingFour">
    <h4 class="panel-title">
      <?= Yii::_t('notifications.main.telegram_push_subscribe')?>
    </h4>
  </div>
  <div class="list-group">
    <?php /* Если не заполнен токен или имя бота Телеграм, не показываем */ ?>
    <div class="list-group-item">
      <?= Yii::_t('notifications.notification_types.telegram') ?>
      <?php if ($isTelegramConfigured): ?>
        <div class="pull-right">
          <?php if ($telegramId): ?>
            <?= Html::a(Html::icon('remove'), ['unsubscribe-telegram'],
              [
                'title' => Yii::_t('notifications.main.unsubscribe'),
                'class' => 'btn btn-xs btn-danger',
                'data-pjax' => 0,
                AjaxButtons::CONFIRM_ATTRIBUTE => Yii::_t('notifications.main.are_you_sure'),
                AjaxButtons::AJAX_ATTRIBUTE => 1,
              ]) ?>
          <?php else: ?>
            <?= \yii\helpers\Html::a(Html::icon('check'), Api::getStartUrl(), [
              'target' => '_blank',
              'class' => 'btn btn-xs btn-success',
              'title' => Yii::_t('notifications.main.subscribe'),
            ]) ?>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="row">
          <div class="col-lg-12 text-muted">
            <?= Yii::_t('notifications.notification_types.telegram_error') ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Push begin -->
    <div class="list-group-item">
      <div class="row">
        <div class="col-lg-12">
          <?= Yii::_t('notifications.notification_types.push') ?>
          <div class="pull-right">
            <?= Html::button(Html::icon('check'), [
              'id' => 'register-push',
              'class' => 'btn btn-xs btn-success',
              'title' => Yii::_t('notifications.main.subscribe'),
              'style' => 'display:none;',
              'data' => [
                'url' => Url::to(['/notifications/settings/subscribe-push/']),
              ]
            ])?>
            <?= Html::button(Html::icon('remove'), [
              'id' => 'delete-push',
              'class' => 'btn btn-xs btn-danger',
              'title' => Yii::_t('notifications.main.unsubscribe'),
              'style' => 'display:none;',
              'data' => [
                'url' => Url::to(['/notifications/settings/unsubscribe-push/']),
              ]
            ])?>
          </div>
        </div>
      </div>
      <div class="row" id="push-error" style="display:none;">
        <div class="col-lg-12 text-muted">
          <?= Yii::_t('notifications.notification_types.push_error') ?>
        </div>
      </div>
    </div>
    <!-- Push End -->
  </div>
</div>
<?php Pjax::end() ?>




<?php Pjax::begin(['id' => 'notificationsPjaxGrid', 'enablePushState' => true]); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    [
      'format' => 'raw',
      'label' => Yii::_t('notifications.module'),
      'filter' => Html::dropDownList(
          Html::getInputName($searchModel, 'module_id'),
          $searchModel->module_id,
          $moduleItems,
          ['class' => 'form-control', 'prompt' => Yii::_t('app.common.choose')]
      ),
      'value' => function($model) {
        return $model->module->module_id;
      },
    ],
    [
      'attribute' => 'event',
      'enableSorting' => false,
      'format' => 'raw',
      'filter' => false,
      'value' => function($model) {
        $eventInstance = Yii::createObject($model->event);
        if (!YII_DEBUG) return $eventInstance->getEventName();

        return \yii\helpers\Html::tag('div', $eventInstance->getEventName());
      }
    ],
    [
      'label' => Yii::_t(Notification::$notificationTypeList[Notification::NOTIFICATION_TYPE_BROWSER]['title']),
      'format' => 'raw',
      'contentOptions' => ['class' => 'text-center'],
      'headerOptions' => ['class' => 'text-center'],
      'filter' => Html::a(Html::icon('times'), ['/notifications/settings/my-notifications-disable/', 'module_id' => $moduleId, 'type' => Notification::NOTIFICATION_TYPE_BROWSER], [
          'title' => Yii::_t('notifications.disable'),
          'class' => 'btn btn-xs btn-danger',
          'data-pjax' => 0,
          'ajaxable-reload' => 1,
          'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
          AjaxButtons::AJAX_ATTRIBUTE => 1,
        ]) . ' ' .Html::a(Html::icon('check'), ['/notifications/settings/my-notifications-enable/', 'module_id' => $moduleId, 'type' => Notification::NOTIFICATION_TYPE_BROWSER], [
          'title' => Yii::_t('notifications.enable'),
          'class' => 'btn btn-xs btn-success',
          'data-pjax' => 0,
          'ajaxable-reload' => 1,
          'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
          AjaxButtons::AJAX_ATTRIBUTE => 1,
      ]),
      'filterOptions' => [
        'class' => 'text-center',
        'style' => 'line-height: 30px',
      ],
      'value' => function($model) {
        /** @var Notification $model */
        $data = $model->getGroupedNotificationsInfo(Notification::NOTIFICATION_TYPE_BROWSER);
        if ($data['id']) {
          if ($data['enabled']) {
            return Html::a(Html::icon('toggle-on', ['class' => 'toggle-on-icon']), ['/notifications/settings/my-notifications-disable/', 'id' => $data['id']], [
              'title' => Yii::_t('notifications.disable'),
              'data-pjax' => 0,
              'ajaxable-reload' => 1,
              'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
              AjaxButtons::AJAX_ATTRIBUTE => 1,
            ]);
          } else {
            return Html::a(Html::icon('toggle-off', ['class' => 'toggle-off-icon']), ['/notifications/settings/my-notifications-enable/', 'id' => $data['id']], [
              'title' => Yii::_t('notifications.enable'),
              'data-pjax' => 0,
              'ajaxable-reload' => 1,
              'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
              AjaxButtons::AJAX_ATTRIBUTE => 1,
            ]);
          }
        }
        return Html::a(Html::icon('toggle-off', ['class' => 'toggle-on-disabled']), 'javascript://', [
          'disabled' => true,
        ]);
      }
    ],
    [
      'label' => Yii::_t(Notification::$notificationTypeList[Notification::NOTIFICATION_TYPE_EMAIL]['title']),
      'format' => 'raw',
      'contentOptions' => ['class' => 'text-center'],
      'headerOptions' => ['class' => 'text-center'],
      'filter' => Html::a(Html::icon('times'), ['/notifications/settings/my-notifications-disable/', 'module_id' => $moduleId, 'type' => Notification::NOTIFICATION_TYPE_EMAIL], [
          'title' => Yii::_t('notifications.disable'),
          'class' => 'btn btn-xs btn-danger',
          'data-pjax' => 0,
          'ajaxable-reload' => 1,
          'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
          AjaxButtons::AJAX_ATTRIBUTE => 1,
        ]) . ' ' . Html::a(Html::icon('check'), ['/notifications/settings/my-notifications-enable/', 'module_id' => $moduleId, 'type' => Notification::NOTIFICATION_TYPE_EMAIL], [
          'title' => Yii::_t('notifications.enable'),
          'class' => 'btn btn-xs btn-success',
          'data-pjax' => 0,
          'ajaxable-reload' => 1,
          'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
          AjaxButtons::AJAX_ATTRIBUTE => 1,
        ]),
      'filterOptions' => [
        'class' => 'text-center',
        'style' => 'line-height: 30px',
      ],
      'value' => function($model) {
        /** @var Notification $model */
        $data = $model->getGroupedNotificationsInfo(Notification::NOTIFICATION_TYPE_EMAIL);
        if ($data['id']) {
          if ($data['enabled']) {
            return Html::a(Html::icon('toggle-on', ['class' => 'toggle-on-icon']), ['/notifications/settings/my-notifications-disable/', 'id' => $data['id']], [
              'title' => Yii::_t('notifications.disable'),
              'data-pjax' => 0,
              'ajaxable-reload' => 1,
              'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
              AjaxButtons::AJAX_ATTRIBUTE => 1,
            ]);
          } else {
            return Html::a(Html::icon('toggle-off', ['class' => 'toggle-off-icon']), ['/notifications/settings/my-notifications-enable/', 'id' => $data['id']], [
              'title' => Yii::_t('notifications.enable'),
              'data-pjax' => 0,
              'ajaxable-reload' => 1,
              'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
              AjaxButtons::AJAX_ATTRIBUTE => 1,
            ]);
          }
        }
        return Html::a(Html::icon('toggle-off', ['class' => 'toggle-on-disabled']), 'javascript://', [
          'disabled' => true,
        ]);
      }
    ],
    [
      'label' => Yii::_t(Notification::$notificationTypeList[Notification::NOTIFICATION_TYPE_TELEGRAM]['title']),
      'format' => 'raw',
      'contentOptions' => ['class' => 'text-center'],
      'headerOptions' => ['class' => 'text-center'],
      'filter' => Html::a(Html::icon('times'), ['/notifications/settings/my-notifications-disable/', 'module_id' => $moduleId, 'type' => Notification::NOTIFICATION_TYPE_TELEGRAM], [
          'title' => Yii::_t('notifications.disable'),
          'class' => 'btn btn-xs btn-danger',
          'data-pjax' => 0,
          'ajaxable-reload' => 1,
          'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
          AjaxButtons::AJAX_ATTRIBUTE => 1,
        ]) . ' ' . Html::a(Html::icon('check'), ['/notifications/settings/my-notifications-enable/', 'module_id' => $moduleId, 'type' => Notification::NOTIFICATION_TYPE_TELEGRAM], [
          'title' => Yii::_t('notifications.enable'),
          'class' => 'btn btn-xs btn-success',
          'data-pjax' => 0,
          'ajaxable-reload' => 1,
          'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
          AjaxButtons::AJAX_ATTRIBUTE => 1,
        ]),
      'filterOptions' => [
        'class' => 'text-center',
        'style' => 'line-height: 30px',
      ],
      'value' => function($model) use ($telegramId) {
        /** @var Notification $model */
        $data = $model->getGroupedNotificationsInfo(Notification::NOTIFICATION_TYPE_TELEGRAM);
        if ($data['id'] && $telegramId) {
          if ($data['enabled']) {
            return Html::a(Html::icon('toggle-on', ['class' => 'toggle-on-icon']), ['/notifications/settings/my-notifications-disable/', 'id' => $data['id']], [
              'title' => Yii::_t('notifications.disable'),
              'data-pjax' => 0,
              'ajaxable-reload' => 1,
              'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
              AjaxButtons::AJAX_ATTRIBUTE => 1,
            ]);
          } else {
            return Html::a(Html::icon('toggle-off', ['class' => 'toggle-off-icon']), ['/notifications/settings/my-notifications-enable/', 'id' => $data['id']], [
              'title' => Yii::_t('notifications.enable'),
              'data-pjax' => 0,
              'ajaxable-reload' => 1,
              'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
              AjaxButtons::AJAX_ATTRIBUTE => 1,
            ]);
          }
        }
        return Html::a(Html::icon('toggle-off', ['class' => 'toggle-on-disabled']), 'javascript://', [
          'disabled' => true,
        ]);
      }
    ],
    [
      'label' => Yii::_t(Notification::$notificationTypeList[Notification::NOTIFICATION_TYPE_PUSH]['title']),
      'format' => 'raw',
      'contentOptions' => ['class' => 'text-center'],
      'headerOptions' => ['class' => 'text-center'],
      'filter' => Html::a(Html::icon('times'), ['/notifications/settings/my-notifications-disable/', 'module_id' => $moduleId, 'type' => Notification::NOTIFICATION_TYPE_PUSH], [
          'title' => Yii::_t('notifications.disable'),
          'class' => 'btn btn-xs btn-danger',
          'data-pjax' => 0,
          'ajaxable-reload' => 1,
          'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
          AjaxButtons::AJAX_ATTRIBUTE => 1,
        ]) . ' ' . Html::a(Html::icon('check'), ['/notifications/settings/my-notifications-enable/', 'module_id' => $moduleId, 'type' => Notification::NOTIFICATION_TYPE_PUSH], [
          'title' => Yii::_t('notifications.enable'),
          'class' => 'btn btn-xs btn-success',
          'data-pjax' => 0,
          'ajaxable-reload' => 1,
          'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
          AjaxButtons::AJAX_ATTRIBUTE => 1,
        ]),
      'filterOptions' => [
        'class' => 'text-center',
        'style' => 'line-height: 30px',
      ],
      'value' => function ($model) use ($isPushEnabled) {
        /** @var Notification $model */
        $data = $model->getGroupedNotificationsInfo(Notification::NOTIFICATION_TYPE_PUSH);
        if ($data['id'] && $isPushEnabled) {
          if ($data['enabled']) {
            return Html::a(Html::icon('toggle-on', ['class' => 'toggle-on-icon']), ['/notifications/settings/my-notifications-disable/', 'id' => $data['id']], [
              'title' => Yii::_t('notifications.disable'),
              'data-pjax' => 0,
              'ajaxable-reload' => 1,
              'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
              AjaxButtons::AJAX_ATTRIBUTE => 1,
            ]);
          } else {
            return Html::a(Html::icon('toggle-off', ['class' => 'toggle-off-icon']), ['/notifications/settings/my-notifications-enable/', 'id' => $data['id']], [
              'title' => Yii::_t('notifications.enable'),
              'data-pjax' => 0,
              'ajaxable-reload' => 1,
              'ajaxable-reload-url' => Url::to(['/notifications/settings/my-notifications']),
              AjaxButtons::AJAX_ATTRIBUTE => 1,
            ]);
          }
        }
        return Html::a(Html::icon('toggle-off', ['class' => 'toggle-on-disabled']), 'javascript://', [
          'disabled' => true,
        ]);
      }
    ],
  ]
]); ?>
<?php Pjax::end(); ?>

