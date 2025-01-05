<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 */

?>
<div class="owl-carousel owl-theme examples-carousel">
    <?php foreach ($data as $page): ?>
        <div class="slider-outter" ontouchstart="startTouch(event)" ontouchend="endTouch(event)">
            <div class="slider-container">
                <img src="<?= $page->getPropByCode('image')->getImageUrl() ?>" alt="#" class="slide-image">
                <div class="slider-info">
                    <p class="clicked_area_info">Info</p>
                    <ul class="slider-info-list">
                        <li>Name: <?= $page->name ?></li>
                        <li>Flow: <?= $page->getPropByCode('flow')->multilang_value ?></li>
                        <li>GEO: <?= $page->getPropByCode('geo')->multilang_value ?></li>
                    </ul>
                </div>
            </div>
            <div class="login-link">
                <a href="" class="show-log show-hide-form">Run with AffShark</a>
            </div>
        </div>
    <?php endforeach ?>
</div>
