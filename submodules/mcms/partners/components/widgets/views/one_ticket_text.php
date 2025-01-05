<?php
use mcms\partners\controllers\SupportController;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/** @var \mcms\support\models\SupportText $model */
/** @var string $avatar */
/** @var boolean $isOwner */
?>

<div class="row">
  <div class="col-xs-6<?= $isOwner ? '' : ' col-xs-offset-6 text-right'?>">
    <div class="ticket-message<?= $isOwner ? '' : ' admin'?>">
      <div class="user_avatar">
        <img src="/img/support.png" alt="">
      </div>
      <div class="ticket-message_wrap">
        <div class="ticket-message_wrap-header">
          <?php $supportName = Html::encode($model->getFromUser()->one()->topname) ? : SupportController::t('manager')?>
          <?= $isOwner ? SupportController::t('myself') : Html::encode($supportName);?>
          <i class="msg_created"><?= Yii::$app->getFormatter()->asRelativeTime($model->created_at);?></i>
        </div>
        <div class="ticket-message_wrap-body">
          <div class="ticket-message_wrap-body__text">
            <?= HtmlPurifier::process($model->text, [
              'Attr.AllowedFrameTargets' => ['_blank'],
            ]) ?>
          </div>
        </div>
        <?php if($model->images): ?>
          <div class="ticket-message_wrap-footer">
            <span><i class="icon-atach"></i> <?= SupportController::t('has_attached_file') ?>:</span>
            <a href="<?= $model->getImageSrc() ?>" data-lightbox="image-<?= $model->id ?>">
              <img src="<?= $model->getImageSrc() ?>" alt="">
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>