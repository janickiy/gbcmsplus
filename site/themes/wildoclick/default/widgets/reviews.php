<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

?>

<h2><?= $category->name ?></h2>
<ul id="response-slider">
  <?php foreach($data as $key=>$page): ?>
    <li class="slide">
      <div class="slide-wrapper">
        <div class="photo icon-<?=!$key%2 ? 1 : 2?>">
          <picture>
            <source scrset="<?= $page->getPropByCode('photo')->getImageUrl() ?>, <?= $page->getPropByCode('photox2')->getImageUrl() ?> 2x" media="all">
            <img src="<?= $page->getPropByCode('photo')->getImageUrl() ?>" srcset="<?= $page->getPropByCode('photox2')->getImageUrl() ?> 2x" alt="">
          </picture>
        </div>
        <div class="content">
          <blockquote>
            <?= $page->text ?>
          </blockquote>
        </div>
      </div>
    </li>
  <?php endforeach ?>
</ul>
