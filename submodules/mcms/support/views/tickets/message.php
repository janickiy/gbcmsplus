<?php
use mcms\user\models\User;
use yii\helpers\HtmlPurifier;
/** @var \mcms\support\models\SupportText $model */
?>
<div class="clearfix panel <?= $model->isOwner ? 'panel-primary' : 'panel-danger' ?>">
  <div class="panel-body">
    <div class="header">
      <strong class="primary-font"><?= $model->fromUser->getViewLink(User::LABEL_TEMPLATE_USERNAME) ?></strong>
      <small class="pull-right text-muted">
        <span class="glyphicon glyphicon-time"></span> <?= $model->messageCreatedAt ?></small>
    </div>
    <p><?= HtmlPurifier::process($model->text, [
        'Attr.AllowedFrameTargets' => ['_blank'],
      ]) ?></p>

    <?php if($model->images):?>
    <p>
      <a href="<?= $model->getImageSrc() ?>" data-lightbox="image-<?= $model->id ?>">
        <img src="<?= $model->getImageSrc() ?>" width="50" data-lightbox="image-<?= $model->id ?>"></a>
    </p>
    <?php endif; ?>
  </div>
</div>