<?php
use mcms\partners\controllers\SupportController;
use yii\helpers\Html;
use yii\helpers\Url;
use mcms\partners\assets\basic\TicketsAsset;

TicketsAsset::register($this);

/** @var \mcms\support\models\Support $model */
?>


<div class="panel ticket<?= (!$model->is_opened ? ' ticket-closed' : ($model->owner_has_unread_messages ? ' has-new' : '')) ?>">
  <div class="ticket-header collapsed" data-messages="<?= Url::to(['support/messages', 'id' => $model->id])?>" data-ticket-id="<?= $model->id ?>" data-parent="#accordion" href="#dialog_<?= $model->id ?>"
       aria-expanded="true" aria-controls="collapseOne">
    <div class="row">
      <div class="col-xs-8">
        <div class="ticket-title"><?= Html::encode($model->name); ?></div>
        <div class="ticket-last_message"><?= Yii::$app->getFormatter()->asRelativeTime($model->created_at);?></div>
      </div>
      <div class="col-xs-4 text-right">
        <span class="ticket-count_msg">
          <span>
            <?php if(!$model->is_opened): ?>
              <?= SupportController::t('is_closed') ?>
            <?php elseif($model->owner_has_unread_messages): ?>
              <?= SupportController::t('has_unread_messages'); ?>
            <?php endif; ?>
          </span>
          <i class="icon-ticket"></i><?= $model->textCount; ?>
        </span>
      </div>
    </div>
  </div>
  <div id="dialog_<?= $model->id ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
    <div class="panel-body"></div>
  </div>
</div>
