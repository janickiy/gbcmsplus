<?php

use kartik\tabs\TabsX;
use yii\widgets\DetailView;
use yii\bootstrap\Html;

/** @var \mcms\notifications\models\NotificationsDelivery $model */
?>

<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"><?= $model->header ?></h4>
</div>

<div class="modal-body fix-modal-image-width">
  <?php $roles = $model->getRolesAsArray() ?>

  <?php if ($roles) { ?>
    <?php $rolesAsString = $roles ? implode(', ', $roles) : null; ?>
    <?= DetailView::widget([
      'model' => $model,
      'attributes' => [
        [
          'attribute' => 'roles',
          'format' => 'raw',
          'value' => Yii::$app->formatter->asRaw($rolesAsString),
        ],
      ],
    ]) ?>
  <?php } ?>

  <?php
  $items = [];
  foreach ($model->message as $language => $value) {
    $items[] = [
      'label' => strtoupper($language),
      'content' => Yii::$app->formatter->asStringOrNull($value),
    ];
  }
  ?>
  <?= TabsX::widget([
    'items' => $items,
    'position' => TabsX::POS_ABOVE,
  ]); ?>

</div>
<div class="modal-footer">
  <?= Html::a(
    Yii::_t('notifications.notifications.view_notifications_list') . ' ' . Html::icon('new-window'),
    $model->getNotificationsUrl(),
    ['data-pjax' => 0, 'class' => 'btn btn-info', 'target' => '_blank']
  ); ?>
  <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
</div>