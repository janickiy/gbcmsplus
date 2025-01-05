<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 */

?>
<div class="owl-carousel partners">
    <?php foreach ($data as $page):
        $image = $page->getPropByCode('image');
        $image = $image ? $image->getImageUrl() : null;
        ?>
        <div class="partners-slide" ontouchstart="startTouch(event)" ontouchend="endTouch(event)">
            <img src="<?= $image ?>" alt="<?= $page->text ?>" class="partner-image">
        </div>
    <?php endforeach ?>
</div>