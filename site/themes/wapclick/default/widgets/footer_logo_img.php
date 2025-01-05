<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('logo');

$image = $images->getImageUrl();
?>

  <a href="/" class="logo">
    <img class="lazy" data-src="<?= $image?>" alt="">
  </a>



