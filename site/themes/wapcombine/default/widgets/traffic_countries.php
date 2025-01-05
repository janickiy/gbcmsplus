<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

use yii\helpers\Html;

?>
<span>
    <?php foreach (array_chunk($data, 4)[0] as $page) : ?>
      <?= Html::img($page->getPropByCode('image')->getImageUrl(),['title'=>$page->name]) ?>
    <?php endforeach; ?>
</span>