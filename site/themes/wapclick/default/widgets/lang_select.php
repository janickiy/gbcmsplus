<?php
use yii\helpers\Url;
use mcms\common\SystemLanguage;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$currentLang = (new SystemLanguage())->getCurrent();
?>

<div class="dropdown">
  <button type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <?php foreach($data as $page): ?>
      <?php if ($page->code !== $currentLang) continue; ?>
      <i class="icon-lang"></i><?= $page->name ?>
    <?php endforeach ?>
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dLabel">
    <?php foreach($data as $page): ?>
      <?php $images = unserialize($page->images); ?>
      <li><a href="<?= Url::to(['users/site/lang', 'language' => $page->code]); ?>"><?= $page->name ?></a></li>
    <?php endforeach ?>
  </ul>
</div>