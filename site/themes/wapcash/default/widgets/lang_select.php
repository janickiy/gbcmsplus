<?php
use yii\helpers\Url;
use mcms\common\SystemLanguage;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$currentLang = (new SystemLanguage())->getCurrent();
?>

<div class="js-lang-form choose-lang-block uk-button-dropdown uk-flex uk-flex-middle uk-hidden-small" data-uk-dropdown="{mode:'click', pos:'bottom-center'}" aria-haspopup="true" aria-expanded="false">
  <button class="choose-lang uk-flex">
          <span class="choose-lang__inner uk-flex uk-flex-middle uk-flex-center">

            <?php foreach($data as $page): ?>
              <?php if ($page->code !== $currentLang) continue; ?>
              <?php $images = unserialize($page->images); ?>
              <img src="<?= $images[0] ?>" alt=""><i class="choose-lang__arr sp-iconsm sp-iconsm__arrow-d"></i>
            <?php endforeach ?>

					</span>
  </button>
  <div class="lang-drop uk-dropdown-blank">
    <?php foreach($data as $page): ?>
      <?php $images = unserialize($page->images); ?>
      <a href="<?= Url::to(['users/site/lang', 'language' => $page->code]); ?>" class="uk-flex uk-flex-middle lang-drop__link <?= $currentLang == $page->code ? 'lang-drop__link_current' : ''?>"><img src="<?= $images[0] ?>" alt=""><?= $page->name ?></a>

    <?php endforeach ?>
  </div>
</div>
