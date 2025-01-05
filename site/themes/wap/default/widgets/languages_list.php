<?php

use yii\helpers\Url;
use mcms\common\SystemLanguage;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$currentLang = (new SystemLanguage())->getCurrent();
?>
<ul class="header__langs">
  <?php
  
  /** @var \mcms\pages\models\Page $page */
  foreach ($data as $page) { ?>
      <li class="header__langs-item">
          <a class="header__lang" <?php if ($currentLang !== $page->code) { ?> href="<?= \yii\helpers\Url::to(['users/site/lang', 'language' => $page->code]); ?>" <?php } ?>>
            <?= $page->name ?>
            <?php
            $image = $page->getPropByCode('country_flag');
            if (!empty($image)) {
              echo \yii\helpers\Html::img($image->getImageUrl());
            } ?>
          </a>
      </li>
  <?php } ?>
</ul>