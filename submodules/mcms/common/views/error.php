<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this View*/
/** @var integer $code */
/** @var string $message */
/** @var string $url */

?>
<div class="error_bg">
  <div class="error_code"><?= $code ?></div>
</div>
<h1><?= Yii::_t('app.errors.error_label', [$code]) ?></h1>
<span><?= $message ?></span>
<?= Html::a(Yii::_t('app.errors.to_main'), $url) ?>