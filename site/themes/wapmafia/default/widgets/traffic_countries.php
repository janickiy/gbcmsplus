<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

use yii\helpers\Html;

?>
<span class="cauntres"><?php foreach ($data as $page) {
        echo $page->name;
        if ($page !== end($data)) {
            echo ', ';
        }
    } ?></span>
<ul>
    <?php foreach ($data as $page) {
        echo Html::tag('li', Html::img($page->getPropByCode('image')->getImageUrl()));
    } ?>
</ul>