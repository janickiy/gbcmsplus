<?php
use yii\helpers\Url;
use mcms\common\SystemLanguage;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$currentLang = (new SystemLanguage())->getCurrent();
?>

<div class="btn-group languages dropdown">
  <button id="languages-drop" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <?php foreach ($data as $page): ?>
      <?php if ($page->code !== $currentLang) {
        continue;
      } ?>
      <?php $images = unserialize($page->images); ?>
      <img src="<?= $images[0] ?>" alt="">
      <span><?= $page->name ?></span>
    <?php endforeach ?>
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-right dropdown-animation" aria-labelledby="languages-drop">
    <?php foreach ($data as $page): ?>
      <?php $images = unserialize($page->images); ?>
      <li>
        <a href="<?= Url::to(['users/site/lang', 'language' => $page->code]); ?>">
          <img src="<?= $images[0] ?>">
          <span><?= $page->name ?></span>
        </a>
      </li>
    <?php endforeach ?>
  </ul>
</div>