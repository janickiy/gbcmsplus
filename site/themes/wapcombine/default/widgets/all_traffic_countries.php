<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

use yii\helpers\Html;

?>
<div class="countries_box">
  <?php foreach ($data as $page) : ?>
    <?= Html::img($page->getPropByCode('image')->getImageUrl(), ['title' => $page->name]) ?>
  <?php endforeach; ?>
</div>