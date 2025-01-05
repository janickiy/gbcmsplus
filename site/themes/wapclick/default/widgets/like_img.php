<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('like_image');

$image = $images->getImageUrl();

?>

<img class="lazy" data-src="<?= $image ?>" alt="" />


