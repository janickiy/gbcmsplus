<?php
use yii\helpers\Url;
use mcms\common\SystemLanguage;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$currentLang = (new SystemLanguage())->getCurrent();
?>




<div class="lang uk-width-medium-1-4 uk-width-medium-1-6 uk-flex uk-flex-middle uk-flex-right">
  <?php foreach($data as $page): ?>
    <?php if ($page->code == $currentLang) continue; ?>
    <?php $images = unserialize($page->images); ?>

    <a href="/users/site/lang/?language=<?= $page->code ?>" class="uk-flex uk-flex-middle uk-flex-right lang__link link link_dotted">
      <div class="lang__icon-cont">
        <i class="sp-icons sp-icons__gb-big" style="background-image: url(<?= $images[0] ?>); background-position: 0 0; background-size: 100% 100%;"></i>
        <i class="lang__opacity"></i>
      </div>
      <span class="link link_dotted uk-text-nowrap"><?= $page->name ?></span>
    </a>

  <?php endforeach ?>
</div>
