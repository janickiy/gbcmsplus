<?php

use mcms\user\models\LoginAttempt;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/**
 * @var View $this
 * @var LoginAttempt $model
 */

?>

<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>

<div class="modal-body">

  <?= DetailView::widget([
    'model' => $model,
    'attributes' => [
      'id',
      [
        'attribute' => 'user_id',
        'format' => 'html',
        'value' => $model->getUserLink(),
      ],
      'login',
      [
        'attribute' => 'fail_reason',
        'value' => $model->getFailReasonLabel(),
      ],
      [
        'attribute' => 'password',
        'visible' => Yii::$app->user->can('UsersLoginAttemptsShowPassword'),
      ],
      'ip',
      'user_agent',
      'created_at:datetime',
    ]
  ]) ?>

</div>
<div class="modal-footer">
  <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
</div>

