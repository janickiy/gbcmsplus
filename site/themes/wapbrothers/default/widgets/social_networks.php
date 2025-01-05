<?php
use yii\helpers\Html;
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<ul class="social">
  <?php foreach($data as $page): ?>
  <?php if (!empty(ArrayHelper::getValue($page->getPropByCode('social_url'), 'multilang_value'))): ?><li><a href="<?= ArrayHelper::getValue($page->getPropByCode('social_url'), 'multilang_value', '#') ?>" target="_blank" class="<?= $page->code ?>"></a></li><?php endif; ?>
  <?php endforeach ?>
</ul>
