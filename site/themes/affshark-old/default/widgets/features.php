<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 */

use yii\helpers\ArrayHelper;

$page1 = ArrayHelper::getValue($data, 0);
$page2 = ArrayHelper::getValue($data, 1);
$page3 = ArrayHelper::getValue($data, 2);
$page4 = ArrayHelper::getValue($data, 3);
$page5 = ArrayHelper::getValue($data, 4);
$page6 = ArrayHelper::getValue($data, 5);
?>

<div style="text-align: center;" id="subcr_1">
    <div class="subcr" id="subcr_iner_1" style="width: 155px;    display: inline-block;margin-left: 20px;"><img
                src="<?= $page1->getPropByCode('image')->getImageUrl() ?>" alt="" style="        width: 45%;">
        <p style="    margin-top: 10px;    font-size: 14px!important;">
            <?= $page1->text ?>
        </p></div>
    <div class="subcr" style="width: 155px;    display: inline-block;"><img
                src="<?= $page2->getPropByCode('image')->getImageUrl() ?>" alt="" style="        width: 45%;">
        <p style="    margin-top: 10px;    font-size: 14px!important;">
            <?= $page2->text ?>
        </p></div>

    <div class="subcr" id="subcr_iner_1_1" style="width: 155px;    display: inline-block;margin-left: 20px;"><img
                src="<?= $page3->getPropByCode('image')->getImageUrl() ?>" alt="" style="        width: 45%;">
        <p style="    margin-top: 10px;    font-size: 14px!important;">
            <?= $page3->text ?>
        </p></div>
</div>
<div style="    margin-top: 30px;text-align: center;" id="subcr_2">
    <div class="subcr" id="subcr_iner_2" style="width: 155px;    display: inline-block;margin-left: 20px;"><img
                src="<?= $page4->getPropByCode('image')->getImageUrl() ?>" alt="" style="        width: 45%;">
        <p style="    margin-top: 10px;    font-size: 14px!important;">
            <?= $page4->text ?>
        </p></div>

    <div class="subcr" style="width: 155px;    display: inline-block;"><img
                src="<?= $page5->getPropByCode('image')->getImageUrl() ?>" alt="" style="        width: 45%;">
        <p style="    margin-top: 10px;    font-size: 14px!important;">
            <?= $page5->text ?><br><br>
        </p></div>
    <div class="subcr" id="subcr_iner_2_1" style="width: 155px;    display: inline-block;margin-left: 20px;"><img
                src="<?= $page6->getPropByCode('image')->getImageUrl() ?>" alt="" style="        width: 45%;">
        <p style="    width: 200px; margin-top: 10px;    font-size: 14px!important;    margin-left: -23px;">
            <?= $page6->text ?>
        </p></div>
    <div class="subcr" id="subcr_iner_2_2"
         style="width: 155px;    display: inline-block;margin-left: 20px; display:none;"><img
                src="<?= $page3->getPropByCode('image')->getImageUrl() ?>" alt="" style="        width: 45%;">
        <p style="    margin-top: 10px;    font-size: 14px!important;">
            <?= $page3->text ?>
        </p></div>
</div>
