<?php
use mcms\common\event\Event;
use mcms\common\helpers\StringEncoderDecoder;
use mcms\notifications\Module;
use yii\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use yii\helpers\Url;

/* @var \mcms\notifications\models\BrowserNotification $model */

$notifyItemClass = ['item'];
if (!$model->is_viewed) {
  $notifyItemClass[] = 'new';

  if ($model->is_news && !$model->is_important) $notifyItemClass[] = 'news';
  if ($model->is_important) $notifyItemClass[] = 'danger';
}
?>


<li class="<?= implode(" ", $notifyItemClass) ?>">
  <?php if (!empty($model->header)): ?>
    <?php
    $urlParts = $model->getEventObjectUrl();
    if (!$model->is_viewed && $urlParts) {
      $urlParts = array_merge($urlParts,
        [Module::FN_QUERY_PARAM => $model->id]
      );
    }
    $url = $urlParts ? $urlParts : null;
    ?>
    <div><strong><?= $url ? Html::a($model->header, $url) : $model->header ?></strong></div>
  <?php endif; ?>
  <span><?= $model->message ?></span>
  <div>
    <small><?= Yii::$app->getFormatter()->asRelativeTime($model->updated_at) ?></small>
    <i class="icon-<?= ArrayHelper::getValue($modules, $model->from_module_id) ?> pull-right"></i>
  </div>
</li>
