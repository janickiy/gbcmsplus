<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 */

$page1 = \yii\helpers\ArrayHelper::getValue($data, 0);
$page2 = \yii\helpers\ArrayHelper::getValue($data, 1);
$page3 = \yii\helpers\ArrayHelper::getValue($data, 2);
$page4 = \yii\helpers\ArrayHelper::getValue($data, 3);
?>
<div class="row">
    <div class="col-sm-2"></div>
    <div class="col-sm-12 center-block">
        <div class="advantages-container flex-start">
            <div class="advantages-item-1 advantages-style">
                <p><?= $page1 ? $page1->text : '' ?></p>
            </div>
            <div class="advantages-item-2 advantages-style">
                <p><?= $page2 ? $page2->text : '' ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-2"></div>
</div>
<div class="row">
    <div class="col-sm-2"></div>
    <div class="col-sm-12 center-block">
        <div class="advantages-container">
            <div class="advantages-item-3 advantages-style">
                <p><?= $page3 ? $page3->text : '' ?></p>
            </div>
            <div class="advantages-item-4 advantages-style">
                <p><?= $page4 ? $page4->text : '' ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-2"></div>
</div>
