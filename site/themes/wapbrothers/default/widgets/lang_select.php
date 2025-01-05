<?php
use yii\helpers\Url;
use mcms\common\SystemLanguage;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$currentLang = (new SystemLanguage())->getCurrent();
?>


<div class="lang-dropdown">
  <?php foreach ($data as $page): ?>
    <?php if ($page->code !== $currentLang) continue; ?>
    <span class="button-dropdown">
								<span><?= strtoupper($page->code) ?></span>
    </span>
  <?php endforeach ?>

  <div class="menu-dropdown">

    <?php foreach ($data as $page): ?>
      <?php $images = unserialize($page->images); ?>
      <a href="<?= Url::to(['users/site/lang', 'language' => $page->code]); ?>"
         class="<?= $page->code ?>-leng <?php if ($page->code == $currentLang): ?>active<?php endif; ?>">
        <span><?= $page->name ?></span>
      </a>
    <?php endforeach ?>

  </div>
</div>