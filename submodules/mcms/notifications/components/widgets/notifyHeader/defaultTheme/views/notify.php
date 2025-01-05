<?php
use mcms\common\event\Event;
use mcms\common\helpers\StringEncoderDecoder;
use mcms\notifications\Module;
use yii\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use yii\helpers\Url;

/* @var \mcms\notifications\models\BrowserNotification $model */
?>

<li<?php if ($model->is_viewed === 1) echo ' class="is_viewed"'?>>
  <?php if (!empty($model->header)): ?>
    <?php
    $urlParts = $model->getEventObjectUrl();
    if (!$model->is_viewed && $urlParts) {
      $modelId = $model->model_id ? : $model->id;
      $urlParts = array_merge($urlParts,
        [Module::FN_QUERY_PARAM => StringEncoderDecoder::encode($modelId)]
      );
    }
    $url = $urlParts ? $urlParts : null;
    ?>
    <div><strong><?= $url ? Html::a($model->header, $url) : $model->header ?></strong></div>
  <?php endif; ?>
  <div><span><?= $model->message ?></span></div>

  <div>
    <small><?= Yii::$app->getFormatter()->asRelativeTime($model->updated_at)?></small>
    <i class="glyphicon glyphicon-<?= ArrayHelper::getValue($modules, $model->from_module_id) ?> pull-right"></i>
  </div>
</li>