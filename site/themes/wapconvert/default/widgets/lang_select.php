<?php
use yii\helpers\Url;
use mcms\common\SystemLanguage;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$currentLang = (new SystemLanguage())->getCurrent();
?>
<a href="#" class="btn btn-border header-controls-lang js-select-lang">
  <?php foreach($data as $page): ?>
    <?php if ($page->code !== $currentLang) continue; ?>
    <?php $image = $page->getPropByCode('image') ?>
    <img class="header-controls-lang-flag icon" src="<?= $image ? $image->getImageUrl() : '' ?>">
  <?php endforeach ?>
  <span class="header-controls-lang-icon"><i class="fa fa-chevron-down" aria-hidden="true"></i></span>
</a>
<ul class="header-controls-lang-list">
  <?php foreach($data as $page): ?>
    <?php $image = $page->getPropByCode('image') ?>
    <li>
      <a href="<?= Url::to(['users/site/lang', 'language' => $page->code]); ?>">
        <img class="header-controls-lang-flag icon" src="<?= $image ? $image->getImageUrl() : '' ?>">
      </a>
    </li>
  <?php endforeach ?>
</ul>