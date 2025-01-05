<?php

use mcms\notifications\models\UserInvitationEmailSent;
use yii\bootstrap\Html;
use yii\widgets\DetailView;

/**
 * @var UserInvitationEmailSent $model
 */

?>
<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"><?= $model->to ?></h4>
</div>

<div class="modal-body">
  <?= DetailView::widget([
    'model' => $model,
    'attributes' => [
      'id',
      [
        'attribute' => 'invitation_email_id',
        'value' => $model->invitationEmail->stringInfo,
      ],
      [
        'attribute' => 'invitation_id',
        'value' => $model->invitation->stringInfo,
      ],
      'from',
      'to',
      'header',
      'message',
      'is_sent:boolean',
      'attempts',
      'created_at:datetime',
      'updated_at:datetime',
    ]
  ]) ?>
</div>

<div class="modal-footer">
  <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
</div>
