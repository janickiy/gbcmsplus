<?php
/* @var mcms\notifications\models\BrowserNotification $model */
/* @var array $modules */
?>
<li class="<?= $model->is_important ? 'danger' : (!$model->is_viewed ? 'new' : '') ?>">
<i class="icon-<?= $modules[$model->from_module_id] ?>"></i>
<?php if (!empty($model->header)): ?>
  <strong><?= $model->header ?></strong>
<?php endif; ?>
<div class="news_item-text"><?= $model->message ?></div>
<div class="news_item-added" data-toggle="tooltip" data-placement="right"  title="<?= Yii::$app->getFormatter()->asDatetime($model->created_at, 'php:d.m.Y - H:i'); ?>"><?= Yii::$app->getFormatter()->asRelativeTime($model->created_at); ?></div>
</li>