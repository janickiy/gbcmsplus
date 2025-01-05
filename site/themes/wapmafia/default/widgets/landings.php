<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */
use yii\helpers\Html;

$i = 1;
?>
<ul id="boutique" class="boutique">
    <?php foreach ($data as $page) {
        echo Html::tag('li', Html::img($page->getPropByCode('image')->getImageUrl()), ['class' => 'li' . $i++]);
    } ?>
</ul>
