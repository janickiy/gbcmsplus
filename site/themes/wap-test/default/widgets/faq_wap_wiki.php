<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<?php foreach ($data as $page) { ?>
    <li class="faq__item">
        <details class="faq__item-question">
            <summary class="faq__item-question-heading">
                            <span class="faq__item-question-heading-box">

                            <span class="faq__item-number"><?= $page->code ?></span>
<?= \yii\helpers\Html::encode($page->name) ?>
                            </span>
            </summary>
            <p class="faq__item-question-body"><?= $page->text ?></p>
        </details>
    </li>
<?php } ?>