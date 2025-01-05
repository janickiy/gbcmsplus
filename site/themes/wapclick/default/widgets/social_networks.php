<?php
use yii\helpers\Html;
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="socials">
  <?php foreach($data as $page): ?>
  <a href="<?= ArrayHelper::getValue($page->getPropByCode('social_url'), 'multilang_value', '#') ?>" target="_blank" class="<?= $page->code ?>"><i class="icon-<?= $page->code ?>"></i></a>
  <?php endforeach ?>
</div>