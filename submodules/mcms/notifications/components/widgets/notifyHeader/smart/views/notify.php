<?php
use mcms\common\event\Event;
use mcms\common\helpers\StringEncoderDecoder;
use mcms\notifications\Module;
use yii\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use yii\helpers\Url;

/* @var \mcms\notifications\models\BrowserNotification $model */

$iconClasses = [
  'users' => 'user',
  'promo' => 'star',
  'support' => 'question',
  'payments' => 'money',
  'pages'=> 'file',
  'statistic' => 'bar-chart',
];

//$modules = array_intersect_key($iconClasses, array_flip($modules));

?>
<li>
		<span class="padding-10 <?= $model->is_viewed !== 1 ? 'unread' : ''?>">

			<em class="badge padding-5 no-border-radius bg-color-blueLight pull-left margin-right-5">
				<i class="fa fa-<?= ArrayHelper::getValue($iconClasses, ArrayHelper::getValue($modules, $model->from_module_id)) ?> fa-fw fa-2x"></i>
			</em>

			<span>
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
          <strong><?= $url && Html::hasUrlAccess($url) ? Html::a($model->header, $url, ['data-pjax' => 0]) : $model->header ?></strong>
        <?php endif; ?>
        <?= $model->message ?>
        <br>
				 <span class="pull-right font-xs text-muted"><i><?= Yii::$app->getFormatter()->asRelativeTime($model->updated_at)?></i></span>
			</span>

		</span>
</li>