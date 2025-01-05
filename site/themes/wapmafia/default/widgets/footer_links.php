<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */


use yii\helpers\Html;

foreach ($data as $page) {
    echo Html::a(
            Html::img($page->getPropByCode('imageUrl')->multilang_value, ['alt' => $page->getPropByCode('alt')->multilang_value]),
            [$page->getPropByCode('url')->multilang_value]
        ) . PHP_EOL;
}