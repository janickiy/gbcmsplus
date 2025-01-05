<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use yii\widgets\Pjax;

$this->blocks['actions'] = $this->render('actions/add', ['model' => $module]);
?>

<?php ContentViewPanel::begin([
  'padding' => false,
]) ?>
<?php Pjax::begin(); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'export' => false,
  'columns' => [
    [
      'attribute' => 'id',
      'width' => '80px',
    ],
    [
      'attribute' => 'event',
      'format' => 'raw',
      'value' => function($model) {
        $eventInstance = Yii::createObject($model->event);
        if (!YII_DEBUG) return $eventInstance->getEventName();

        return
          \yii\helpers\Html::tag('div', $eventInstance->getEventName())
          . \yii\helpers\Html::tag('div', \yii\helpers\Html::tag('strong', $eventInstance::class), ['class' => 'pull-right'])
         ;
      }
    ],
    [
      'attribute' => 'roles',
      'format' => 'html',
      'value' => function ($model) {
        /** @var \mcms\notifications\models\Notification $model */
        $roles = $model->getRolesToShow();
        return $roles
          ? implode(', ', $roles)
          : '<span class="text-muted">' . Yii::_t('notifications.notifications.roles_not_selected') . '</span>';
      },
      'visible' => Yii::$app->user->can('NotificationsSettingsEdit'),
    ],
    [
      'attribute' => 'notification_type',
      'value' => 'namedNotificationType'
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update} {disable} {enable} {delete}',
      'contentOptions' => ['style' => 'width: 100px'],
    ],

  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>