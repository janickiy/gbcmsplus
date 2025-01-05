<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 */

$page = \yii\helpers\ArrayHelper::getValue($data, 0);

if (!$page) {
    return;
}
$items = ($items = $page->getPropByCode('items')) ?: [];
?>
<h2 class="for-private-section-header left-text"><?= $page->name ?></h2>
<div class="container">
    <div class="row">
        <div class="center-block">
            <ul class="private-list">
                <?php foreach ($items as $item): ?>
                    <li class="private-item"><span class="underline"><?= $item->multilang_value ?></span></li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
</div>
